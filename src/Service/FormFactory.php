<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\BaseEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;

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
        $type = $type ?: 'App\\Form\\' . $entity->getEntityType() . 'Type';

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
}
