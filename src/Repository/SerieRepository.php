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

use App\Entity\Kinder;
use App\Entity\Serie;
use App\Exception\KinderVirtualException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Serie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Serie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Serie[]    findAll()
 */
class SerieRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Serie::class);
    }

    public function copyVirtual(?Serie $from, ?Serie $to)
    {
        if (!$from || !$to) {
            return;
        }
        $alreadyVirtuals = [];
        foreach ($to->getKinders() as $kinder) {
            if ($kinder->isVirtual()) {
                $alreadyVirtuals[$kinder->getOriginal()->getId()] = true;
            }
        }
        $em = $this->getEntityManager();
        foreach ($from->getKinders() as $kinder) {
            $kinder = $kinder->isVirtual() ? $kinder->getOriginal() : $kinder;
            if (!isset($alreadyVirtuals[$kinder->getId()])) {
                try {
                    $newKinder = new Kinder();
                    $newKinder->setName($kinder->getName());
                    $newKinder->setSerie($to);
                    $newKinder->setOriginal($kinder);
                    $em->persist($newKinder);
                } catch (KinderVirtualException $e) {
                }
            }
        }
        $em->flush();
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     * @return Serie[]
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
