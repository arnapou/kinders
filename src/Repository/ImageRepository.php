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
use App\Entity\Image;
use App\Service\SearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 */
class ImageRepository extends ServiceEntityRepository
{
    /**
     * @var SearchFilter
     */
    private $searchFilter;

    public function __construct(RegistryInterface $registry, SearchFilter $searchFilter)
    {
        parent::__construct($registry, Image::class);
        $this->searchFilter = $searchFilter;
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :val')
            ->setParameter('val', $type)
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchQB(array $values): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        foreach ($values as $value) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('i.type', $qb->expr()->literal("%$value%")),
                $qb->expr()->like('i.name', $qb->expr()->literal("%$value%")),
                $qb->expr()->like('i.file', $qb->expr()->literal("%$value%"))
            ));
        }
        return $qb
            ->addOrderBy('i.type', 'ASC')
            ->addOrderBy('i.name', 'ASC');
    }

    public function searchAll()
    {
        if (!($values = $this->searchFilter->values())) {
            return $this->findAll();
        }
        return $this->searchQB($values)
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

    public function getTypes(): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->select('a.type')
                ->orderBy('a.type', 'ASC')
                ->groupBy('a.type')
                ->getQuery()
                ->getResult(),
            'type'
        );
    }

    // /**
    //  * @return Image[] Returns an array of Image objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Image
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
