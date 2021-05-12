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
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        private ImageHelper $helper
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('tn', [$this, 'thumbnail']),
            new TwigFilter('href', [$this, 'href']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vich_uploader_asset', [$this, 'vich_uploader_asset']),
        ];
    }

    public function href($object)
    {
        if ($object instanceof Image) {
            return $this->helper->asset($object);
        }

        return (string) $object;
    }

    public function thumbnail($filename, int $w = 0, int $h = 0)
    {
        return $this->helper->thumbnail($filename, $w, $h);
    }

    /**
     * @param Image $object
     *
     * @deprecated présent que pour comaptibilité avec l'extension native
     */
    public function vich_uploader_asset($object): ?string
    {
        return $this->helper->asset($object);
    }
}
