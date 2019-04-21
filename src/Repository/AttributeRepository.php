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
use App\Service\SearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribute[]    findAll()
 */
class AttributeRepository extends ServiceEntityRepository
{
    /**
     * @var SearchFilter
     */
    private $searchFilter;

    public function __construct(RegistryInterface $registry, SearchFilter $searchFilter)
    {
        parent::__construct($registry, Attribute::class);
        $this->searchFilter = $searchFilter;
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :val')
            ->setParameter('val', $type)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchAll()
    {
        if (!($values = $this->searchFilter->values())) {
            return $this->findAll();
        }
        $qb = $this->createQueryBuilder('a');
        foreach ($values as $value) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('a.type', $qb->expr()->literal("%$value%")),
                $qb->expr()->like('a.name', $qb->expr()->literal("%$value%"))
            ));
        }
        return $qb
            ->addOrderBy('a.type', 'ASC')
            ->addOrderBy('a.name', 'ASC')
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
        return parent::findBy($criteria, $orderBy ?: ['type' => 'ASC', 'name' => 'ASC'], $limit, $offset);
    }

    // /**
    //  * @return Attribute[] Returns an array of Attribute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attribute
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
