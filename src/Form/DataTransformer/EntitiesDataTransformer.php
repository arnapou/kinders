<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\Entity\Image;
use Tetranz\Select2EntityBundle\Form\DataTransformer\EntitiesToPropertyTransformer;

class EntitiesDataTransformer extends EntitiesToPropertyTransformer
{
    /**
     * @param Image[] $entities
     *
     * @return array
     */
    public function transform($entities)
    {
        if (empty($entities)) {
            return [];
        }

        $data = [];

        foreach ($entities as $entity) {
            $data[$entity->getId()] = $entity;
        }

        return $data;
    }
}
