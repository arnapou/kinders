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

use App\Entity\BaseItem;
use Tetranz\Select2EntityBundle\Form\DataTransformer\EntityToPropertyTransformer;

class EntityDataTransformer extends EntityToPropertyTransformer
{
    /**
     * Transform entity to array.
     *
     * @param BaseItem $entity
     *
     * @return array
     */
    public function transform($entity)
    {
        return $entity && $entity->getId() ? [$entity->getId() => $entity] : [];
    }
}
