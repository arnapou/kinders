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

use App\Entity\BPZ;
use App\Entity\Collection;
use App\Entity\Kinder;
use App\Entity\MenuItem;
use App\Entity\Serie;
use App\Entity\ZBA;
use App\Presenter\Front\CollectionPresenter;
use App\Presenter\Front\SeriePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class FrontSearch
{
    use FrontQueryToolTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FrontCache $frontCache,
        private Security $security
    ) {
    }

    public function getSeriesByCollection(MenuItem $menuItem): array
    {
        $series = $this->querySeries($menuItem);
        $seriesP = [];
        $collections = [];
        foreach ($series as $serie) {
            $collection = $serie->getCollection();

            $id = $collection && $this->getSeriesCount($collection) > 1
                ? $collection->getId()
                : 0;

            $collections[$id] ??= new CollectionPresenter($collection);
            $presenter = new SeriePresenter($serie);
            $collections[$id]->series[] = $presenter;
            $seriesP[$serie->getId()] = $presenter;
        }

        $kinders = $this->queryKinders(array_keys($series));

        $this->populateCountry($seriesP);
        $this->populateImage($seriesP, $kinders);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->populateComplete($seriesP, $kinders);
        }

        $this->sortCollections($collections);

        return $collections;
    }

    /**
     * Une série est considérée complete si on n'a aucun champ lookingFor à TRUE pour aucun Kinder, BPZ ou ZBA de la série.
     *
     * @param SeriePresenter[] $series
     */
    private function populateComplete(array $series, array $kinders): void
    {
        $kinderIds = array_keys($kinders);
        $bpzs = $this->queryBpzs($kinderIds);
        $zbas = $this->queryZbas($kinderIds);

        foreach ($kinders as $kinder) {
            $series[$kinder->getSerie()->getId()]->complete &= !$kinder->isLookingFor();
        }

        foreach ($bpzs as $bpz) {
            $kinder = $kinders[$bpz->getKinder()->getId()];
            $series[$kinder->getSerie()->getId()]->complete &= !$bpz->isLookingFor();
        }

        foreach ($zbas as $zba) {
            $kinder = $kinders[$zba->getKinder()->getId()];
            $series[$kinder->getSerie()->getId()]->complete &= !$zba->isLookingFor();
        }
    }

    /**
     * @return ZBA[]
     */
    private function queryZbas(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(ZBA::class, 'e', 'e.id')
            ->where('e.kinder IN (:ids)')
            ->setParameter('ids', $kinderIds)
            ->getQuery()->getResult();
    }

    /**
     * @return BPZ[]
     */
    private function queryBpzs(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(BPZ::class, 'e', 'e.id')
            ->where('e.kinder IN (:ids)')
            ->setParameter('ids', $kinderIds)
            ->getQuery()->getResult();
    }

    /**
     * @return Serie[]
     */
    private function querySeries(MenuItem $menuItem): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Serie::class, 'e', 'e.id')
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

    /**
     * Optimisation de requêtes doctrine.
     */
    private function getSeriesCount(Collection $collection): int
    {
        return $this->frontCache->from(
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
