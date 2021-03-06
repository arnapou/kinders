<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type\Multiple;

use App\Entity\Image;
use App\Form\DataTransformer\EntitiesDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ImageListType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'class' => Image::class,
            'primary_key' => 'id',
            'text_property' => 'name',
            'minimum_input_length' => 0,
            'page_limit' => 20,
            'allow_clear' => true,
            'delay' => 250,
            'cache' => true,
            'cache_timeout' => 60000,
            'language' => 'fr',
            'placeholder' => 'images ...',
            'auto_start' => false,
            'transformer' => EntitiesDataTransformer::class,
        ]);
    }

    public function getParent()
    {
        return Select2EntityType::class;
    }
}
