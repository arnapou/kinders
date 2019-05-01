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
            new TwigFilter('imagetype', [$this, 'imagetype']),
        ];
    }

    public function thumbnail($filename)
    {
        $infos = pathinfo($filename);
        return $infos['dirname'] . '/' . $infos['filename'] . '_tn.' . $infos['extension'];
    }

    public function imagetype($object)
    {
        if (\is_object($object)) {
            $reflection = new \ReflectionClass($object);
            return $reflection->getShortName();
        }
        return '';
    }
}
