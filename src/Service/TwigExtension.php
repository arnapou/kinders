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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('tn', [$this, 'thumbnail']),
        ];
    }

    public function thumbnail($filename, int $w = 0, int $h = 0)
    {
        $infos = pathinfo($filename);
        if ($w && $h) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.${w}x${h}." . $infos['extension'];
        }

        if ($w) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.${w}." . $infos['extension'];
        }

        if ($h) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.x${h}." . $infos['extension'];
        }

        return $infos['dirname'] . '/' . $infos['filename'] . '_tn.' . $infos['extension'];
    }
}
