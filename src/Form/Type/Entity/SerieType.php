<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type\Entity;

use App\Entity\Serie;
use App\Form\Type\Multiple\AttributesListType;
use App\Form\Type\Multiple\ImageListType;
use App\Form\Type\Select\CollectionSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['autofocus' => true]])
            ->add('year', IntegerType::class, ['required' => false, 'empty_data' => 0])
            ->add('country')
            ->add('collection', CollectionSelectType::class, ['remote_route' => 'admin_series_autocomplete'])
            ->add('attributes', AttributesListType::class)
            ->add('images', ImageListType::class, [                'remote_route' => 'admin_series_autocomplete'            ])
            ->add('comment', TextareaType::class, ['required' => false, 'empty_data' => '']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Serie::class,
        ]);
    }
}