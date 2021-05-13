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

use App\Entity\MenuCategory;
use App\Entity\MenuItem;
use App\Form\Type\Multiple\AttributesListType;
use App\Form\Type\YearType;
use App\Service\PublicRoutes;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    public function __construct(
        private PublicRoutes $publicRoutes)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['autofocus' => true]])
            ->add('minYear', YearType::class)
            ->add('maxYear', YearType::class)
            ->add('sorting', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('routeName', ChoiceType::class, [
                'required' => false,
                'empty_data' => '',
                'choices' => array_merge(['' => ''], $this->publicRoutes->names()),
            ])
            ->add('category', EntityType::class, [
                'class' => MenuCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->orderBy('u.sorting', 'ASC')->addOrderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('attributes', AttributesListType::class)
            ->add('comment', TextareaType::class, ['required' => false, 'empty_data' => '']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
        ]);
    }
}
