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
    protected function increment(Kinder $kinder): int
    {
        return $kinder->getQuantityDouble();
    }

    /**
     * @return Kinder[]
     */
    protected function queryKinders(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('k')
            ->from(Kinder::class, 'k')
            ->join('k.serie', 's')
            ->andWhere('k.quantityDouble > 0')
            ->getQuery()->getResult();
    }

    /**
     * @return BPZ[]
     */
    protected function queryBpzs(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e, k')
            ->from(BPZ::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.quantityDouble > 0')
            ->getQuery()->getResult();
    }

    /**
     * @return ZBA[]
     */
    protected function queryZbas(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e, k')
            ->from(ZBA::class, 'e')
            ->join('e.kinder', 'k')
            ->join('k.serie', 's')
            ->andWhere('e.quantityDouble > 0')
            ->getQuery()->getResult();
    }
}
