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

use App\Entity\Serie;

class FrontSerie
{
    public function getKinderReferences(Serie $serie): array
    {
        $refs = [];
        foreach ($serie->getKinders() as $kinder) {
            if ($kinder->getReference() && !isset($refs[$kinder->getReference()])) {
                $refs[$kinder->getReference()] = $kinder->getName();
            }
        }
        return $refs;
    }
}
