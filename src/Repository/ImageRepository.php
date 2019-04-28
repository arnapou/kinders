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

use App\Entity\BaseItem;
use App\Entity\BPZ;
use App\Entity\Image;
use App\Entity\Item;
use App\Entity\Kinder;
use App\Entity\Piece;
use App\Entity\Serie;
use App\Entity\ZBA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Image::class);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     * @return Image[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy ?: ['type' => 'ASC', 'name' => 'ASC'], $limit, $offset);
    }

    public function getTypes(): array
    {
        $types = [];
        $em    = $this->getEntityManager();
        $meta  = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $reflectionClass = new \ReflectionClass($m->getName());
            if ($reflectionClass->isInstantiable() && $reflectionClass->isSubclassOf(BaseItem::class)) {
                $types[] = $reflectionClass->getShortName();
            }
        }
        return array_combine($types, $types);
    }

    public function linked(?Image $image): bool
    {
        if (null === $image) {
            return false;
        }
        $classes = [
            BPZ::class,
            ZBA::class,
            Kinder::class,
            Piece::class,
            Item::class,
            Serie::class,
        ];
        foreach ($classes as $class) {
            if ($objects = $this->linkedObjects($image, $class)) {
                var_dump($objects);
                return true;
            }
        }
        return false;
    }

    public function linkedObjects(Image $image, string $class): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->from($class, 'o')
            ->select('o')
            ->join('o.images', 'i', Join::WITH, 'i.id = :id')
            ->setParameter('id', $image->getId())
            ->getQuery()->getResult();
    }

    /**
     * @param Image $image
     * @return BPZ[]
     */
    public function linkedBPZ(Image $image)
    {
        return $this->linkedObjects($image, BPZ::class);
    }

    /**
     * @param Image $image
     * @return ZBA[]
     */
    public function linkedZBA(Image $image)
    {
        return $this->linkedObjects($image, ZBA::class);
    }

    /**
     * @param Image $image
     * @return Kinder[]
     */
    public function linkedKinder(Image $image)
    {
        return $this->linkedObjects($image, Kinder::class);
    }

    /**
     * @param Image $image
     * @return Piece[]
     */
    public function linkedPiece(Image $image)
    {
        return $this->linkedObjects($image, Piece::class);
    }

    /**
     * @param Image $image
     * @return Serie[]
     */
    public function linkedSerie(Image $image)
    {
        return $this->linkedObjects($image, Serie::class);
    }

    /**
     * @param Image $image
     * @return Item[]
     */
    public function linkedItem(Image $image)
    {
        return $this->linkedObjects($image, Item::class);
    }

    public static function getTypeFrom($class): string
    {
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->getShortName();
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
