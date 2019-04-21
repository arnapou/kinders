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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchFilter
{
    /**
     * @var array
     */
    private $cachedValues = [];
    /**
     * @var string
     */
    private $defaultRouteName = '';
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager)
    {
        $this->container     = $container;
        $this->entityManager = $entityManager;
    }

    public function searchQueryBuilder(ServiceEntityRepository $repository, array $values): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($repository->getClassName(), 'e');

        $fields = $this->getStringFieldNames($repository);
        foreach ($values as $value) {
            $ors = [];
            foreach ($fields as $field) {
                $ors[] = $qb->expr()->like("e.$field", $qb->expr()->literal("%$value%"));
            }
            $qb->andWhere($qb->expr()->orX(...$ors));
        }

        if (\in_array('type', $fields)) {
            $qb->addOrderBy('e.type', 'ASC');
        }
        $qb->addOrderBy('e.name', 'ASC');

        return $qb;
    }

    public function search(ServiceEntityRepository $repository): array
    {
        return $this
            ->searchQueryBuilder($repository, $this->values())
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
        if ($value !== '') {
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
        $fields   = [];
        $metadata = $this->entityManager->getClassMetadata($repository->getClassName());
        foreach ($metadata->getFieldNames() as $fieldName) {
            $map = $metadata->getFieldMapping($fieldName);
            if (($map['type'] ?? '') === 'string') {
                $fields[] = $fieldName;
            }
        }
        return $fields;
    }
}
