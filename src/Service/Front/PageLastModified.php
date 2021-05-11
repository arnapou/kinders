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
use App\Entity\MenuItem;
use App\Entity\Serie;
use App\Entity\ZBA;

class PageLastModified extends PageSearch
{
    private const NOMBRE = 20;

    protected function getRestrictedSerieIds(?MenuItem $menuItem): array
    {
        $all = array_merge(
            $this->entityManager->createQueryBuilder()
                ->select('e.id, e.updatedAt as dt')
                ->from(Serie::class, 'e')
                ->orderBy('dt', 'DESC')
                ->setMaxResults(self::NOMBRE)
                ->getQuery()->getResult(),

            $this->entityManager->createQueryBuilder()
                ->select('s.id, MAX(e.updatedAt) as dt')
                ->from(Kinder::class, 'e')
                ->join('e.serie', 's')
                ->groupBy('e.serie')
                ->orderBy('dt', 'DESC')
                ->setMaxResults(self::NOMBRE)
                ->getQuery()->getResult(),

            $this->entityManager->createQueryBuilder()
                ->select('s.id, MAX(e.updatedAt) as dt')
                ->from(BPZ::class, 'e')
                ->join('e.kinder', 'k')
                ->join('k.serie', 's')
                ->groupBy('k.serie')
                ->orderBy('dt', 'DESC')
                ->setMaxResults(self::NOMBRE)
                ->getQuery()->getResult(),

            $this->entityManager->createQueryBuilder()
                ->select('s.id, MAX(e.updatedAt) as dt')
                ->from(ZBA::class, 'e')
                ->join('e.kinder', 'k')
                ->join('k.serie', 's')
                ->groupBy('k.serie')
                ->orderBy('dt', 'DESC')
                ->setMaxResults(self::NOMBRE)
                ->getQuery()->getResult()
        );

        uasort($all, static fn ($a, $b) => -($a['dt'] <=> $b['dt']));

        $limited = \array_slice($all, 0, self::NOMBRE);

        return array_column($limited, 'id');
    }
}
