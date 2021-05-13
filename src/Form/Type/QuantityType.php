<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'required' => false,
                'empty_data' => 0,
                'attr' => ['min' => 0, 'step' => 1, 'max' => 999],
            ]
        );
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
