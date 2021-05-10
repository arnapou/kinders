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

use App\Form\Type\Select\SerieSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SerieCopyVirtualType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serie_from', SerieSelectType::class, ['remote_route' => 'admin_series_autocomplete', 'empty_data' => $options['serie_from'] ?? null])
            ->add('serie_to', SerieSelectType::class, ['remote_route' => 'admin_series_autocomplete', 'empty_data' => $options['serie_to'] ?? null]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'serie_from' => '',
            'serie_to' => '',
        ]);
    }
}
