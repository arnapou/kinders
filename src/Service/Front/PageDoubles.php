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
use App\Entity\ZBA;

class PageDoubles extends PageLookingFor
{
    /**
     * @return Kinder[]
     */
    protected function queryStatsKinders(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('SUM(k.quantityDouble) as nb, s.id')
            ->from(Kinder::class, 'k')
            ->join('k.serie', 's')
            ->andWhere('k.quantityDouble > 0')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }

    /**
     * @return BPZ[]
     */
    protected function queryStatsBpzs(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('SUM(k.quantityDouble) as nb, s.id')
            ->from(BPZ::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('k.quantityDouble > 0')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }

    /**
     * @return ZBA[]
     */
    protected function queryStatsZbas(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('SUM(k.quantityDouble) as nb, s.id')
            ->from(ZBA::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('k.quantityDouble > 0')
            ->groupBy('k.serie')
            ->getQuery()->getResult();
    }
}
