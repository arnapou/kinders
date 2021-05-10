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

use App\Entity\Attribute;
use App\Entity\BPZ;
use App\Entity\Image;
use App\Entity\Item;
use App\Entity\Kinder;
use App\Entity\MenuCategory;
use App\Entity\MenuItem;
use App\Entity\Piece;
use App\Entity\Serie;
use App\Entity\ZBA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchFilter
{
    private array  $cachedValues = [];
    private string $defaultRouteName = '';

    public function __construct(
        private ContainerInterface $container,
        private EntityManagerInterface $entityManager,
        private Pagination $pagination
    ) {
    }

    public function searchQueryBuilder(ServiceEntityRepository $repository, array $values): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($repository->getClassName(), 'e');

        $this->addJoins($repository->getClassName(), $qb);

        $fields = $this->getStringFieldNames($repository);
        foreach ($values as $value) {
            $ors = [];
            foreach ($fields as $field) {
                $ors[] = $qb->expr()->like("e.$field", $qb->expr()->literal("%$value%"));
            }
            $qb->andWhere($qb->expr()->orX(...$ors));
        }

        $this->addOrderBy($repository->getClassName(), $qb);

        return $qb;
    }

    public function search(ServiceEntityRepository $repository): array
    {
        $qb = $this->searchQueryBuilder($repository, $this->values());
        $count = $qb
            ->select($qb->expr()->count('e'))
            ->getQuery()->getSingleScalarResult();

        $this->pagination->setItemCount($count);

        return $this
            ->searchQueryBuilder($repository, $this->values())
            ->setMaxResults($this->pagination->getPageSize())
            ->setFirstResult($this->pagination->offsetStart())
            ->getQuery()
            ->getResult();
    }

    public function value(?string $routeName = null): string
    {
        $routeName = $routeName ?: $this->defaultRouteName;
        if (!$routeName) {
            return '';
        }

        if (!\array_key_exists($routeName, $this->cachedValues)) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
            $session = $request->getSession();

            $search = $request->get('search');
            if (null !== $search) {
                $session->set("search.$routeName", $search);
            } else {
                $search = $session->get("search.$routeName") ?: '';
            }

            $this->cachedValues[$routeName] = trim($search ?: '');
        }

        return $this->cachedValues[$routeName];
    }

    public function values(?string $routeName = null): array
    {
        $value = $this->value($routeName);
        if ('' !== $value) {
            $value = preg_replace('!\s+!', ' ', $value);

            return explode(' ', $value);
        }

        return [];
    }

    public function isVisible(): bool
    {
        return $this->defaultRouteName ? true : false;
    }

    public function getRouteName(): string
    {
        return $this->defaultRouteName;
    }

    public function setRouteName(string $defaultRouteName): void
    {
        $this->defaultRouteName = $defaultRouteName;
    }

    private function getStringFieldNames(ServiceEntityRepository $repository): array
    {
        $fields = [];
        $metadata = $this->entityManager->getClassMetadata($repository->getClassName());
        foreach ($metadata->getFieldNames() as $fieldName) {
            $map = $metadata->getFieldMapping($fieldName);
            if (($map['type'] ?? '') === 'string') {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }

    private function addJoins(string $class, QueryBuilder $qb): QueryBuilder
    {
        return match ($class) {
            Kinder::class,
            Piece::class,
            Item::class => $qb
                ->join('e.serie', 's'),
            ZBA::class,
            BPZ::class => $qb
                ->join('e.kinder', 'k')
                ->join('k.serie', 's'),
            MenuItem::class => $qb
                ->join('e.category', 'c'),
            default => $qb,
        };
    }

    private function addOrderBy(string $class, QueryBuilder $qb): QueryBuilder
    {
        return match ($class) {
            Attribute::class => $qb
                ->addOrderBy('e.type')
                ->addOrderBy('e.name'),
            Image::class => $qb
                ->addOrderBy('e.linked')
                ->addOrderBy('e.type')
                ->addOrderBy('e.name'),
            Serie::class => $qb
                ->addOrderBy('e.year', 'DESC')
                ->addOrderBy('e.name'),
            Kinder::class => $qb
                ->addOrderBy('s.name')
                ->addOrderBy('e.year', 'DESC')
                ->addOrderBy('e.name'),
            Piece::class,
            Item::class => $qb
                ->addOrderBy('s.year', 'DESC')
                ->addOrderBy('s.name')
                ->addOrderBy('e.name'),
            ZBA::class,
            BPZ::class => $qb
                ->addOrderBy('s.name')
                ->addOrderBy('k.year', 'DESC')
                ->addOrderBy('e.name'),
            MenuCategory::class => $qb
                ->addOrderBy('e.sorting')
                ->addOrderBy('e.name'),
            MenuItem::class => $qb
                ->addOrderBy('c.sorting')
                ->addOrderBy('e.sorting')
                ->addOrderBy('e.minYear', 'DESC')
                ->addOrderBy('e.name'),
            default => $qb
                ->addOrderBy('e.name', 'ASC'),
        };
    }
}
