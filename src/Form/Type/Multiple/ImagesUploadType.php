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

use App\Form\Type\SimpleImageType;
use App\Repository\ImageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImagesUploadType extends AbstractType
{
    public const NB_IMAGES = 10;
    /**
     * @var ImageRepository
     */
    private $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => $this->imageRepository->getTypes(),
            'empty_data' => $options['image_type'] ?? '',
        ]);
        for ($nb = 1; $nb <= self::NB_IMAGES; ++$nb) {
            $builder->add("image$nb", SimpleImageType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'image_type' => '',
        ]);
    }
}
