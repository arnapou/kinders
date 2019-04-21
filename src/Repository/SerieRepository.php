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

use App\Entity\Attribute;
use App\Entity\Serie;
use App\Service\SearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Serie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Serie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Serie[]    findAll()
 */
class SerieRepository extends ServiceEntityRepository
{
    /**
     * @var SearchFilter
     */
    private $searchFilter;

    public function __construct(RegistryInterface $registry, SearchFilter $searchFilter)
    {
        parent::__construct($registry, Serie::class);
        $this->searchFilter = $searchFilter;
    }

    public function searchAll()
    {
        if (!($values = $this->searchFilter->values())) {
            return $this->findAll();
        }
        $qb = $this->createQueryBuilder('p');
        foreach ($values as $value) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('p.reference', $qb->expr()->literal("%$value%")),
                $qb->expr()->like('p.name', $qb->expr()->literal("%$value%")),
                $qb->expr()->like('p.year', $qb->expr()->literal("%$value%"))
            ));
        }
        return $qb
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     * @return Attribute[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy ?: ['name' => 'ASC'], $limit, $offset);
    }

    // /**
    //  * @return Serie[] Returns an array of Serie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Serie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
