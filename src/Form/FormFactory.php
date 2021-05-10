<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\BaseEntity;
use App\Form\Type\Entity\BPZType;
use App\Form\Type\Entity\ImageType;
use App\Form\Type\Entity\ItemType;
use App\Form\Type\Entity\KinderType;
use App\Form\Type\Entity\PieceType;
use App\Form\Type\Entity\ZBAType;
use App\Repository\ImageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class FormFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function detectOptions($type, object $entity): array
    {
        switch ($type) {
            case ImageType::class:
                return ['image_type' => $entity->getType()];
            case BPZType::class:
            case ZBAType::class:
                return ['kinder' => $entity->getKinder()];
            case KinderType::class:
            case ItemType::class:
            case PieceType::class:
                return ['serie' => $entity->getSerie()];
            default:
                return [];
        }
    }

    public function create(BaseEntity $entity, ?string $type = null, array $options = []): ?FormInterface
    {
        $type = $type ?: __NAMESPACE__ . '\\Type\\Entity\\' . ImageRepository::getTypeFrom($entity) . 'Type';

        $options += $this->detectOptions($type, $entity);

        $form = $this->container->get('form.factory')->create($type, $entity, $options);
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            $entityManager = $this->container->get('doctrine')->getManager();
            $entityManager->persist($entity);
            $entityManager->flush();

            return null;
        }

        return $form;
    }

    public function renderAdd(string $view, ?BaseEntity $entity, array $options = [], array $context = [], string $type = null): ?Response
    {
        return $this->render($view, $entity, $options, array_merge($context, ['action' => 'Créer']), $type);
    }

    public function renderEdit(string $view, ?BaseEntity $entity, array $options = [], array $context = [], string $type = null): ?Response
    {
        return $this->render($view, $entity, $options, array_merge($context, ['action' => 'Modifier']), $type);
    }

    public function render(string $view, ?BaseEntity $entity, array $options = [], array $context = [], string $type = null): ?Response
    {
        if (null === $entity) {
            return null;
        }

        if ($form = $this->create($entity, $type, $options)) {
            $context = array_merge($context, [
                'item' => $entity,
                'form' => $form->createView(),
            ]);

            if ($this->container->has('templating')) {
                $content = $this->container->get('templating')->render($view, $context);
            } elseif ($this->container->has('twig')) {
                $content = $this->container->get('twig')->render($view, $context);
            } else {
                throw new \LogicException('Cannot render Form from FormFactory');
            }

            return new Response($content);
        }

        return null;
    }
}
