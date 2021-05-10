<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class ImageThumbnailController extends AbstractController
{
    private int $tnSize;
    private int $tnExpire;
    private FilesystemAdapter $cache;
    private string $path;
    private array $mimeTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
    ];

    public function __construct(ContainerInterface $container, KernelInterface $kernel)
    {
        $this->tnSize = (int) $container->getParameter('thumbnail.size');
        $this->tnExpire = (int) $container->getParameter('thumbnail.expire');
        $this->path = $kernel->getProjectDir() . '/public/img';
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @Route("/img/{path}_tn.{ext}",         requirements={"path": "[a-zA-Z0-9]+/[a-z0-9]/[a-z0-9]{8}", "ext": "jpg|png"}, name="image_thumbnail")
     * @Route("/img/{path}_tn.{w}.{ext}",     requirements={"path": "[a-zA-Z0-9]+/[a-z0-9]/[a-z0-9]{8}", "ext": "jpg|png", "w": "\d+"}, name="image_thumbnail_w")
     * @Route("/img/{path}_tn.x{h}.{ext}",    requirements={"path": "[a-zA-Z0-9]+/[a-z0-9]/[a-z0-9]{8}", "ext": "jpg|png", "h": "\d+"}, name="image_thumbnail_h")
     * @Route("/img/{path}_tn.{w}x{h}.{ext}", requirements={"path": "[a-zA-Z0-9]+/[a-z0-9]/[a-z0-9]{8}", "ext": "jpg|png", "w": "\d+", "h": "\d+"}, name="image_thumbnail_wh")
     */
    public function thumbnailAction(string $path, string $ext, int $w = 0, int $h = 0)
    {
        $this->sanitizeWH($w, $h);
        $filename = $this->path . "/$path.$ext";
        if (is_file($filename)) {
            $filemtime = filemtime($filename);
            $filesize = filesize($filename);
            $content = $this->cache->get(
                "${w}x${h}" . md5($path) . ".$ext.$filemtime.$filesize",
                function (ItemInterface $item) use ($filename, $ext, $w, $h) {
                    $item->expiresAfter($this->tnExpire);

                    return $this->imgResize($filename, $ext, $w, $h);
                }
            );

            return $this->fileResponse($content, $ext, $filemtime);
        }
    }

    private function fileResponse($content, $ext, $filemtime): Response
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', $this->mimeTypes[$ext]);
        $response->setCache([
            'etag' => base64_encode(hash('sha256', $content, true)),
            'last_modified' => \DateTime::createFromFormat('U', $filemtime),
            'max_age' => 864000,
            's_maxage' => 864000,
            'public' => true,
        ]);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

        return $response;
    }

    private function imgResize(string $filename, string $ext, int $width, int $height): string
    {
        if (class_exists('Imagick')) {
            return $this->imgResizeImagick($filename, $ext, $width, $height);
        }

        if (\function_exists('imagecreatefromjpeg')) {
            return $this->imgResizeGD($filename, $ext, $width, $height);
        }

        throw new \RuntimeException();
    }

    private function imgResizeImagick(string $filename, string $ext, int $width, int $height): string
    {
        $img = new \Imagick($filename);
        $w1 = $img->getImageWidth();
        $h1 = $img->getImageHeight();
        [$w2, $h2] = $this->newSize($w1, $h1, $width, $height);
        $img->resizeImage($w2, $h2, \Imagick::FILTER_LANCZOS, 1);

        return $img->getImageBlob();
    }

    private function imgResizeGD(string $filename, string $ext, int $width, int $height): string
    {
        $resize = function ($img) use ($width, $height) {
            $w1 = imagesx($img);
            $h1 = imagesy($img);
            [$w2, $h2] = $this->newSize($w1, $h1, $width, $height);
            $dst = imagecreate($w2, $h2);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $w2, $h2, $w1, $h1);

            return $dst;
        };
        switch ($ext) {
            case 'jpg':
                ob_start();
                imagejpeg($resize(imagecreatefromjpeg($filename)), null, 95);

                return ob_get_clean();
            case 'png':
                ob_start();
                imagepng($resize(imagecreatefrompng($filename)), null, 9);

                return ob_get_clean();
        }
        throw new \RuntimeException();
    }

    private function newSize(float $w1, float $h1, float $width, float $height): array
    {
        if ($w1 / $h1 > $width / $height) {
            return [$width, floor($width * $h1 / $w1)];
        }

        return [floor($height * $w1 / $h1), $height];
    }

    private function sanitizeWH(int &$w, int &$h)
    {
        if (0 === $w && 0 === $h) {
            $w = $h = $this->tnSize;
        } elseif (0 === $w) {
            $w = 2000;
        } elseif (0 === $h) {
            $h = 2000;
        }
    }
}
