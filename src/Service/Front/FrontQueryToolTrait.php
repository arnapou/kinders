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

use App\Entity\Country;
use App\Entity\Kinder;
use App\Entity\Serie;

trait FrontQueryToolTrait
{
    /**
     * @return Country[]
     */
    private function queryCountries(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Country::class, 'e', 'e.id')
            ->getQuery()->getResult();
    }

    /**
     * @return Kinder[]
     */
    private function queryKinders(array $serieIds): array
    {
        if (!$serieIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Kinder::class, 'e', 'e.id')
            ->where('e.serie IN (:ids)')
            ->setParameter('ids', $serieIds)
            ->getQuery()->getResult();
    }

    /**
     * Défini un item country à la main pour économiser doctrine.
     */
    private function populateCountry(array $series)
    {
        $countries = $this->queryCountries();

        foreach ($series as $serie) {
            $serie->country = $countries[$serie->getCountry()->getId()];
        }
    }

    /**
     * Définit la première image de la série.
     */
    private function populateImage(array $series, array $kinders)
    {
        $kinderIds = array_keys($kinders);
        $images = $this->queryKinderImages($kinderIds);

        foreach ($images as $kinder) {
            if (null !== $series[$kinder->getSerie()->getId()]->image) {
                continue;
            }
            if ($image = $kinder->getImage()) {
                $series[$kinder->getSerie()->getId()]->image = $image;
            }
        }
    }

    /**
     * @return Kinder[]
     */
    private function queryKinderImages(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e, i')
            ->from(Kinder::class, 'e', 'e.id')
            ->join('e.images', 'i')
            ->where('e.id IN (:ids)');

        foreach (Serie::KINDER_SORTING as $field => $order) {
            $qb->addOrderBy("e.$field", $order);
        }

        return $qb
            ->setParameter('ids', $kinderIds)
            ->getQuery()->getResult();
    }
}
