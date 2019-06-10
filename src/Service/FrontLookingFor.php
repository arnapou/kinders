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

use App\Entity\BPZ;
use App\Entity\Kinder;
use App\Entity\Serie;
use App\Entity\ZBA;
use Doctrine\ORM\EntityManagerInterface;

class FrontLookingFor
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getSeries(): array
    {
        $stats = [];
        foreach ($this->findKinders() as $item) {
            $serieid                    = $item->getSerie()->getId();
            $stats[$serieid]['kinders'] = ($stats[$serieid]['kinders'] ?? 0) + 1;
        }
        foreach ($this->findBpzs() as $item) {
            $serieid                 = $item->getKinder()->getSerie()->getId();
            $stats[$serieid]['bpzs'] = ($stats[$serieid]['bpzs'] ?? 0) + 1;
        }
        foreach ($this->findZbas() as $item) {
            $serieid                 = $item->getKinder()->getSerie()->getId();
            $stats[$serieid]['zbas'] = ($stats[$serieid]['zbas'] ?? 0) + 1;
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Serie::class, 's')
            ->join('s.country', 'c')
            ->andWhere('s.id IN (:ids)')
            ->setParameter(':ids', array_keys($stats))
            ->addOrderBy('s.year', 'DESC')
            ->addOrderBy('c.sorting', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        $series = [];
        foreach ($qb->getQuery()->getResult() as $serie) {
            $series[] = array_merge(['serie' => $serie], $stats[$serie->getId()] ?? []);
        }
        return $series;
    }

    /**
     * @return Kinder[]
     */
    private function findKinders(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('k')
            ->from(Kinder::class, 'k')
            ->join('k.serie', 's')
            ->andWhere('k.lookingFor = true')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BPZ[]
     */
    private function findBpzs(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(BPZ::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ZBA[]
     */
    private function findZbas(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(ZBA::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.lookingFor = true')
            ->getQuery()
            ->getResult();
    }
}
