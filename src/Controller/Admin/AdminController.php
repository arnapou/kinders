<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\BaseEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @var int
     */
    private $nbBars = 32;

    /**
     * @Route("/", name="admin")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        return $this->render(
            '@admin/index.html.twig',
            [
                'stats' => $this->stats($entityManager),
            ]
        );
    }

    private function stats(EntityManagerInterface $entityManager): array
    {
        $stats = [];
        $allMetadata = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($allMetadata as $metadata) {
            $reflectionClass = new \ReflectionClass($metadata->getName());
            if ($reflectionClass->isInstantiable()) {
                $stats[$reflectionClass->getShortName()] = [
                    'count' => $this->statCount($entityManager, $metadata),
                    'created_day' => $this->statDay($entityManager, $metadata, 'createdAt', new \DateInterval('P1D')),
                    'updated_day' => $this->statDay($entityManager, $metadata, 'updatedAt', new \DateInterval('P1D')),
                    'created_week' => $this->statDay($entityManager, $metadata, 'createdAt', new \DateInterval('P1W')),
                    'updated_week' => $this->statDay($entityManager, $metadata, 'updatedAt', new \DateInterval('P1W')),
                ];
            }
        }
        ksort($stats);

        return $stats;
    }

    private function qb(EntityManagerInterface $entityManager, ClassMetadata $metadata)
    {
        $qb = $entityManager->createQueryBuilder();

        return $qb
            ->select($qb->expr()->count('e'))
            ->from($metadata->getName(), 'e');
    }

    private function statCount(EntityManagerInterface $entityManager, ClassMetadata $metadata): int
    {
        try {
            $result = $this->qb($entityManager, $metadata)->getQuery()->getArrayResult()[0] ?? [0];

            return $result ? (int) current($result) : 0;
        } catch (\Throwable $exception) {
            return 0;
        }
    }

    private function statDay(EntityManagerInterface $entityManager, ClassMetadata $metadata, string $field, \DateInterval $interval): array
    {
        $values = [];
        if (is_subclass_of($metadata->getName(), BaseEntity::class)) {
            $date = new \DateTime();
            $date->setTime(0, 0, 0);
            for ($i = 0; $i < $this->nbBars; ++$i) {
                $dateTo = clone $date;
                $dateTo->add($interval);
                $values[] = (int) $this->qb($entityManager, $metadata)
                    ->andWhere("e.$field >= :date1 AND e.$field < :date2")
                    ->setParameter('date1', $date)
                    ->setParameter('date2', $dateTo)
                    ->getQuery()->getSingleScalarResult();
                $date = $date->sub($interval);
            }
        }

        return array_reverse($values);
    }
}
