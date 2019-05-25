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

use App\Entity\Kinder;
use App\Form\Type\BooleanType;
use App\Form\Type\Multiple\AttributesListType;
use App\Form\Type\Multiple\ImageListType;
use App\Form\Type\Select\KinderSelectType;
use App\Form\Type\Select\SerieSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KinderVirtualType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['autofocus' => true]])
            ->add('quantityOwned', IntegerType::class, ['required' => false, 'empty_data' => 0])
            ->add('quantityDouble', IntegerType::class, ['required' => false, 'empty_data' => 0])
            ->add('reference', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('sorting', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('lookingFor', BooleanType::class)
            ->add('year', IntegerType::class, ['required' => false, 'empty_data' => 0])
            ->add('serie', SerieSelectType::class, ['remote_route' => 'admin_kinders_autocomplete', 'empty_data' => $options['serie'] ?? null])
//            ->add('attributes', AttributesListType::class)
//            ->add('images', ImageListType::class, ['remote_route' => 'admin_kinders_autocomplete'])
            ->add('original', KinderSelectType::class, ['remote_route' => 'admin_kinders_autocomplete', 'empty_data' => $options['original'] ?? null])
            ->add('comment', TextareaType::class, ['required' => false, 'empty_data' => '']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kinder::class,
            'serie'      => null,
        ]);
    }
}
