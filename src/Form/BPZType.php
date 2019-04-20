<?php

namespace App\Form;

use App\Entity\BPZ;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BPZType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantityOwned')
            ->add('quantityDouble')
            ->add('reference')
            ->add('lookingFor')
            ->add('year')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('name')
            ->add('comment')
            ->add('kinder')
            ->add('images')
            ->add('attributes')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BPZ::class,
        ]);
    }
}
