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

use App\Entity\Attribute;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributesListType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'       => Attribute::class,
            'multiple'    => true,
            'expanded'    => true,
            'attr'        => ['class' => 'attributeslist'],
//            'choice_attr' => function (Attribute $choice, $key, $value) {
//                return [
//                    'class' => 'attrtype.' . strtolower($choice->getType()),
//                ];
//            },
            'group_by'    => function (Attribute $choice, $key, $value) {
                return $choice->getType();
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'attributes_list';
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
