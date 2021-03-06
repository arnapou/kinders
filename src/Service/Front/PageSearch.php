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

use App\Entity\Collection;
use App\Entity\MenuItem;
use App\Entity\Serie;
use App\Presenter\CollectionPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class PageSearch
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected FrontTool $tool,
        protected Security $security
    ) {
    }

    public function getSeriesByCollection(?MenuItem $menuItem): array
    {
        $series = array_map(static fn (Serie $s) => new \App\Presenter\SeriePresenter($s), $this->querySeries($menuItem));
        $collections = [];
        foreach ($series as $serie) {
            $collection = $serie->getCollection();

            $id = $collection && $this->getSeriesCount($collection) > 1
                ? $collection->getId()
                : 0;

            $collections[$id] ??= new CollectionPresenter($collection);
            $collections[$id]->series[] = $serie;
        }

        $kinders = $this->tool->queryAllKinders(array_keys($series));

        $this->tool->populateCountry($series);
        $this->tool->populateImage($series, $kinders);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->tool->populateComplete($series, $kinders);
        }

        $this->sortCollections($collections);

        return $collections;
    }

    /**
     * @return array<int, Serie>
     */
    protected function querySeries(?MenuItem $menuItem): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Serie::class, 'e', 'e.id')
            ->join('e.country', 'c');

        if ($serieIds = $this->getRestrictedSerieIds($menuItem)) {
            $qb->where('e.id IN (:ids)');
            $qb->setParameter('ids', $serieIds);
        }

        if ($menuItem && $menuItem->getMinYear()) {
            $qb->andWhere('e.year >= :minYear')->setParameter(':minYear', $menuItem->getMinYear());
            $qb->andWhere('e.year <= :maxYear')->setParameter(':maxYear', $menuItem->getMaxYear());
        }

        if ($menuItem && $menuItem->getAttributes()->count()) {
            $qb->join('e.attributes', 'a');
            $qb->andWhere('a IN (:attributes)')->setParameter(':attributes', $menuItem->getAttributes());
        }

        $qb->addOrderBy('e.year', 'DESC');
        $qb->addOrderBy('c.sorting', 'ASC');
        $qb->addOrderBy('c.name', 'ASC');
        $qb->addOrderBy('e.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    protected function getRestrictedSerieIds(?MenuItem $menuItem): array
    {
        return [];
    }

    /**
     * Optimisation de requêtes doctrine.
     */
    private function getSeriesCount(Collection $collection): int
    {
        return $this->tool->cached(
            'CollectionSeriesCount_' . $collection->getId(),
            fn () => $collection->getSeries()->count()
        );
    }

    private function sortCollections(array &$collections): void
    {
        usort(
            $collections,
            static function (CollectionPresenter $a, CollectionPresenter $b) {
                if (!$a->getId()) {
                    return -1;
                }

                if (!$b->getId()) {
                    return 1;
                }

                return $a->getName() <=> $b->getName();
            }
        );
    }
}
