<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\ZBA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZBA|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZBA|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZBA[]    findAll()
 */
class ZBARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZBA::class);
    }

    /**
     * @param null $limit
     * @param null $offset
     *
     * @return ZBA[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy ?: ['name' => 'ASC'], $limit, $offset);
    }

    // /**
    //  * @return ZBA[] Returns an array of ZBA objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ZBA
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
