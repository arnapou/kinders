<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Front;

use App\Entity\Image;
use App\Entity\Kinder;
use App\Entity\Serie;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;

class PageRandom
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function getRandomSerie(): ?Serie
    {
        $imageId = $this->getRandomImageId();
        if (!$imageId) {
            return null;
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Serie::class, 'e')
            ->join('e.kinders', 'k')
            ->join('k.images', 'i')
            ->where('i.id = :id')
            ->setParameter(':id', $imageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getRandomKinderImage(): ?Image
    {
        $imageId = $this->getRandomImageId();
        if (!$imageId) {
            return null;
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Image::class, 'e')
            ->where('e.id = :id')
            ->setParameter(':id', $imageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    protected function getRandomImageId(): ?int
    {
        $ids = $this->entityManager->createQueryBuilder()
            ->select('e.id')
            ->from(Image::class, 'e')
            ->where('e.type = :type')
            ->setParameter(':type', ImageRepository::getTypeFrom(Kinder::class))
            ->getQuery()->getResult();

        if (!$ids) {
            return null;
        }

        $random = random_int(0, \count($ids) - 1);

        return (int) $ids[$random]['id'];
    }
}
