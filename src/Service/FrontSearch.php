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

use App\Entity\MenuItem;
use App\Entity\Serie;
use Doctrine\ORM\EntityManagerInterface;

class FrontSearch
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param MenuItem $menuItem
     * @return Serie[]
     */
    public function getSeries(MenuItem $menuItem): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Serie::class, 'e')
            ->join('e.country', 'c');

        if ($menuItem->getMinYear()) {
            $qb->andWhere('e.year >= :minYear')->setParameter(':minYear', $menuItem->getMinYear());
            $qb->andWhere('e.year <= :maxYear')->setParameter(':maxYear', $menuItem->getMaxYear());
        }
        if ($menuItem->getAttributes()->count()) {
            $qb->join('e.attributes', 'a');
            $qb->andWhere('a IN (:attributes)')->setParameter(':attributes', $menuItem->getAttributes());
        }

        $qb->addOrderBy('e.year', 'DESC');
        $qb->addOrderBy('c.sorting', 'ASC');
        $qb->addOrderBy('c.name', 'ASC');
        $qb->addOrderBy('e.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getSeriesByCollection(MenuItem $menuItem): array
    {
        $collections = [];
        foreach ($this->getSeries($menuItem) as $serie) {
            $collection = $serie->getCollection();
            if ($collection && $collection->getSeries()->count() > 1) {
                $collections[$collection->getId()]['collection'] = $collection;
                $collections[$collection->getId()]['series'][]   = $serie;
            } else {
                $collections[0]['series'][] = $serie;
            }
        }

        usort($collections, function ($a, $b) {
            if (!isset($a['collection'])) {
                return -1;
            } elseif (!isset($b['collection'])) {
                return 1;
            } else {
                return $a['collection']->getName() <=> $b['collection']->getName();
            }
        });

        return $collections;
    }
}
