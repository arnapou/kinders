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
use App\Entity\Kinder;
use App\Entity\Serie;
use App\Entity\ZBA;
use App\Presenter\KinderPresenter;
use App\Presenter\SeriePresenter;
use Doctrine\ORM\EntityManagerInterface;

class PageLookingFor
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected FrontTool $tool
    ) {
    }

    public function getSeries(): array
    {
        $stats = $this->queryStats(
            $realKinders = $this->queryKinders(),
            $realBpzs = $this->queryBpzs(),
            $realZbas = $this->queryZbas(),
        );

        $qb = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Serie::class, 's', 's.id')
            ->join('s.country', 'c')
            ->andWhere('s.id IN (:ids)')
            ->setParameter(':ids', array_keys($stats))
            ->addOrderBy('s.year', 'DESC')
            ->addOrderBy('c.sorting', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        $series = array_map(static fn (Serie $s) => new SeriePresenter($s), $qb->getQuery()->getResult());
        $kinders = $this->tool->queryAllKinders(array_keys($series));

        $this->tool->populateCountry($series);
        $this->tool->populateImage($series, $kinders);
        $this->tool->populateKinders($series, $this->getKindersPresenter($realKinders, $realBpzs, $realZbas));

        foreach ($series as $serie) {
            $serie->stats['kinder'] += $stats[$serie->getId()]['kinder'] ?? 0;
            $serie->stats['bpz'] += $stats[$serie->getId()]['bpz'] ?? 0;
            $serie->stats['zba'] += $stats[$serie->getId()]['zba'] ?? 0;
        }

        return $series;
    }

    /**
     * @param array<Kinder> $kinders
     * @param array<BPZ>    $bpzs
     * @param array<ZBA>    $zbas
     *
     * @return array<int, KinderPresenter}>
     */
    private function getKindersPresenter(array $kinders, array $bpzs, array $zbas): array
    {
        $items = [];

        foreach ($kinders as $item) {
            $id = $item->getId();
            $items[$id] ??= new KinderPresenter($item);
            $items[$id]->flag['kinder'] = true;
        }

        foreach ($bpzs as $item) {
            $id = $item->getKinder()->getId();
            $items[$id] ??= new \App\Presenter\KinderPresenter($item->getKinder());
            $items[$id]->flag['bpz'] = true;
        }

        foreach ($zbas as $item) {
            $id = $item->getKinder()->getId();
            $items[$id] ??= new \App\Presenter\KinderPresenter($item->getKinder());
            $items[$id]->flag['zba'] = true;
        }

        return $items;
    }

    /**
     * @param array<Kinder> $kinders
     * @param array<BPZ>    $bpzs
     * @param array<ZBA>    $zbas
     *
     * @return array<int, array{kinder: count, zba: count, bpz: count}>
     */
    private function queryStats(array $kinders, array $bpzs, array $zbas): array
    {
        $stats = [];

        foreach ($kinders as $item) {
            $id = $item->getSerie()->getId();
            $stats[$id]['kinder'] = ($stats[$id]['kinder'] ?? 0) + $this->increment($item);
        }

        foreach ($bpzs as $item) {
            $id = $item->getKinder()->getSerie()->getId();
            $stats[$id]['bpz'] = ($stats[$id]['bpz'] ?? 0) + $this->increment($item->getKinder());
        }

        foreach ($zbas as $item) {
            $id = $item->getKinder()->getSerie()->getId();
            $stats[$id]['zba'] = ($stats[$id]['zba'] ?? 0) + $this->increment($item->getKinder());
        }

        return $stats;
    }

    protected function increment(Kinder $kinder): int
    {
        return 1;
    }

    /**
     * @return array<int, Kinder>
     */
    protected function queryKinders(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('k')
            ->from(Kinder::class, 'k')
            ->join('k.serie', 's')
            ->andWhere('k.lookingFor = true')
            ->getQuery()->getResult();
    }

    /**
     * @return array<int, BPZ>
     */
    protected function queryBpzs(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e, k')
            ->from(BPZ::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->getQuery()->getResult();
    }

    /**
     * @return array<int, ZBA>
     */
    protected function queryZbas(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e, k')
            ->from(ZBA::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->getQuery()->getResult();
    }
}
