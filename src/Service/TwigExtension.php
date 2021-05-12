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
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vich_uploader_asset', [$this, 'vich_uploader_asset']),
        ];
    }

    public function thumbnail($filename, int $w = 0, int $h = 0)
    {
        return $this->helper->thumbnail($filename, $w, $h);
    }

    /**
     * @param Image $object
     */
    public function vich_uploader_asset($object): ?string
    {
        return $this->helper->asset($object);
    }
}
