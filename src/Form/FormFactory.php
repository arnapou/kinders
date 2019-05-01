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

    public function create(BaseEntity $entity, ?string $type = null): ?FormInterface
    {
        $type = $type ?: __NAMESPACE__ . '\\Type\\Entity\\' . ImageRepository::getTypeFrom($entity) . 'Type';

        $form = $this->container->get('form.factory')->create($type, $entity);

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

    public function render(string $view, ?BaseEntity $entity, string $action): ?Response
    {
        if (null === $entity) {
            return null;
        }

        if ($form = $this->create($entity)) {
            $context = [
                'item'   => $entity,
                'form'   => $form->createView(),
                'action' => $action,
            ];

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
