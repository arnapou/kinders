<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Image;
use App\Form\VichStorage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Remplit la fonction de UploaderHelper pour pouvoir gérer le fieldName par défaut
 * et centraliser d'autres mécaniques utiles.
 *
 * @property VichStorage $storage
 */
class ImageHelper extends \Vich\UploaderBundle\Templating\Helper\UploaderHelper
{
    /**
     * @param Image $obj
     *
     * @return string|null
     */
    public function asset($obj, ?string $fieldName = Image::VICH_FIELD, ?string $className = null)
    {
        return parent::asset($obj, $fieldName, null);
    }

    public function getUploadDestination(): string
    {
        return $this->storage
            ->getFactory()
            ->fromField(null, Image::VICH_FIELD, Image::class)
            ->getUploadDestination();
    }

    public function thumbnail($filename, int $w = 0, int $h = 0): ?string
    {
        $infos = pathinfo($filename);
        if (empty($infos['extension'])) {
            return null;
        }

        $resize = '_tn.';
        if ($w && $h) {
            $resize = ".${w}x${h}";
        } elseif ($w) {
            $resize = ".${w}";
        } elseif ($h) {
            $resize = ".x${h}";
        }

        return $infos['dirname'] . '/' . $infos['filename'] . '_tn' . $resize . '.' . $infos['extension'];
    }

    /**
     * @return null|array{path: string, ext: string}
     */
    public function thumbnailRouteParameters(?Image $image): ?array
    {
        if (null === $image) {
            return null;
        }

        $path = $this->asset($image);
        if (str_starts_with($path, Image::PUBLIC_DIR)) {
            $path = substr($path, \strlen(Image::PUBLIC_DIR));
        }
        $infos = pathinfo(ltrim($path, '/'));

        if (!\in_array($infos['extension'] ?? null, Image::EXTENSIONS, true)) {
            throw new NotFoundHttpException('Not Found');
        }

        return [
            'path' => $infos['dirname'] . '/' . $infos['filename'],
            'ext' => $infos['extension'],
        ];
    }
}
