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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        return $this->render('@admin/index.html.twig', [
            'stats' => $this->stats($entityManager),
        ]);
    }

    private function stats(EntityManagerInterface $entityManager): array
    {
        $stats = [];
        $meta  = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $reflectionClass = new \ReflectionClass($m->getName());
            if ($reflectionClass->isInstantiable()) {
                $repo  = $entityManager->getRepository($m->getName());
                $qb    = $entityManager->createQueryBuilder();
                $count = $qb
                    ->select($qb->expr()->count('e'))
                    ->from($m->getName(), 'e')
                    ->getQuery()->getSingleScalarResult();

                $stats[$reflectionClass->getShortName()] = $count;
            }
        }
        ksort($stats);
        return $stats;
    }
}
