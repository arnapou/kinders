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
use App\Presenter\Front\SeriePresenter;
use Doctrine\ORM\EntityManagerInterface;

class FrontLookingFor
{
    use FrontQueryToolTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getSeries(): array
    {
        $stats = $this->queryStats();

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

        $seriesP = array_map(static fn (Serie $s) => new SeriePresenter($s), $qb->getQuery()->getResult());
        $kinders = $this->queryKinders(array_keys($seriesP));

        $this->populateCountry($seriesP);
        $this->populateImage($seriesP, $kinders);

        foreach ($seriesP as $serie) {
            $serie->statsCount['kinder'] += $stats[$serie->getId()]['kinder'] ?? 0;
            $serie->statsCount['bpz'] += $stats[$serie->getId()]['bpz'] ?? 0;
            $serie->statsCount['zba'] += $stats[$serie->getId()]['zba'] ?? 0;
        }

        return $seriesP;
    }

    /**
     * @return array<int, array{kinder: count, zba: count, bpz: count}>
     */
    private function queryStats(): array
    {
        $stats = [];

        foreach ($this->queryStatsKinders() as $item) {
            $stats[$item['id']]['kinder'] = ($stats[$item['id']]['kinder'] ?? 0) + $item['nb'];
        }

        foreach ($this->queryStatsBpzs() as $item) {
            $stats[$item['id']]['bpz'] = ($stats[$item['id']]['bpz'] ?? 0) + $item['nb'];
        }

        foreach ($this->queryStatsZbas() as $item) {
            $stats[$item['id']]['zba'] = ($stats[$item['id']]['zba'] ?? 0) + $item['nb'];
        }

        return $stats;
    }

    /**
     * @return Kinder[]
     */
    private function queryStatsKinders(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(k) as nb, s.id')
            ->from(Kinder::class, 'k')
            ->join('k.serie', 's')
            ->andWhere('k.lookingFor = true')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }

    /**
     * @return BPZ[]
     */
    private function queryStatsBpzs(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(e) as nb, s.id')
            ->from(BPZ::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }

    /**
     * @return ZBA[]
     */
    private function queryStatsZbas(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(e) as nb, s.id')
            ->from(ZBA::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }
}
