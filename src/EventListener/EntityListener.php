<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Entity\BaseItem;
use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Psr\Log\LoggerInterface;

class EntityListener
{
    /**
     * @var ImageRepository
     */
    private $imageRepository;
    /**
     * @var Image[]
     */
    private $images = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ImageRepository $imageRepository, LoggerInterface $logger)
    {
        $this->imageRepository = $imageRepository;
        $this->logger = $logger;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $entities = $this->changedEntities($uow);

        foreach ($entities as $entity) {
            if ($entity instanceof Image) {
                $this->images[$entity->getId()] = $entity;
            }
            if ($entity instanceof BaseItem) {
                foreach ($entity->getImages() as $image) {
                    $this->images[$image->getId()] = $image;
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $images = $this->images;
        $this->images = [];

        if ($images) {
            foreach ($images as $image) {
                if ($this->checkImage($image)) {
                    $em->persist($image);
                }
            }
            $em->flush();
        }
    }

    private function checkImage(Image $image): bool
    {
        $linked = $this->imageRepository->linked($image);
        if ($linked !== $image->isLinked()) {
            $image->setLinked($linked);

            return true;
        }

        return false;
    }

    private function changedEntities(UnitOfWork $uow): array
    {
        $entities = [];
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $entities[] = $entity;
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $entities[] = $entity;
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $entities[] = $entity;
        }
        foreach ($uow->getScheduledCollectionDeletions() as $collection) {
            /** @var PersistentCollection $collection */
            foreach ($collection->getDeleteDiff() as $entity) {
                $entities[] = $entity;
            }
            foreach ($collection->getDeleteDiff() as $entity) {
                $entities[] = $entity;
            }
        }
        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            foreach ($collection->getDeleteDiff() as $entity) {
                $entities[] = $entity;
            }
            foreach ($collection->getDeleteDiff() as $entity) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }
}
