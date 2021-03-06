<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Front;

use App\Entity\BPZ;
use App\Entity\Country;
use App\Entity\Kinder;
use App\Entity\Serie;
use App\Entity\ZBA;
use App\Presenter\KinderPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class FrontTool
{
    private array $memory = [];

    public function __construct(
        private CacheItemPoolInterface $cache,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function cached(string $key, callable $factory, int $ttl = 15)
    {
        if (\array_key_exists($key, $this->memory)) {
            return $this->memory[$key];
        }

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set($factory());
            $item->expiresAfter($ttl);
            $this->cache->save($item);
        }

        return $this->memory[$key] = $item->get();
    }

    /**
     * Défini un item country à la main pour économiser doctrine.
     *
     * @param array<int, \App\Presenter\SeriePresenter> $series
     */
    public function populateCountry(array $series): void
    {
        $countries = $this->queryCountries();

        foreach ($series as $serie) {
            $serie->country = $countries[$serie->getCountry()->getId()];
        }
    }

    /**
     * Définit la première image de la série.
     *
     * @param array<int, \App\Presenter\SeriePresenter> $series
     * @param array<int, Kinder>                        $kinders
     */
    public function populateImage(array $series, array $kinders): void
    {
        $images = $this->queryKinderImages(array_keys($kinders));

        foreach ($images as $kinder) {
            if (null !== $series[$kinder->getSerie()->getId()]->image) {
                continue;
            }
            if ($image = $kinder->getImage()) {
                $series[$kinder->getSerie()->getId()]->image = $image;
            }
        }
    }

    /**
     * Une série est considérée complete si on n'a aucun champ lookingFor à TRUE pour aucun Kinder, BPZ ou ZBA de la série.
     *
     * @param array<int, \App\Presenter\SeriePresenter> $series
     * @param array<int, Kinder>                        $kinders
     */
    public function populateComplete(array $series, array $kinders): void
    {
        $kinderIds = array_keys($kinders);
        $bpzs = $this->queryBpzs($kinderIds);
        $zbas = $this->queryZbas($kinderIds);

        foreach ($kinders as $kinder) {
            $series[$kinder->getSerie()->getId()]->complete &= !$kinder->isLookingFor();
        }

        foreach ($bpzs as $bpz) {
            $kinder = $kinders[$bpz->getKinder()->getId()];
            $series[$kinder->getSerie()->getId()]->complete &= !$bpz->isLookingFor();
        }

        foreach ($zbas as $zba) {
            $kinder = $kinders[$zba->getKinder()->getId()];
            $series[$kinder->getSerie()->getId()]->complete &= !$zba->isLookingFor();
        }
    }

    /**
     * Remplace les kinders par la liste réelle en direct pour économiser des requêtes.
     *
     * @param array<int, \App\Presenter\SeriePresenter> $series
     * @param array<int, Kinder|KinderPresenter>        $kinders
     */
    public function populateKinders(array $series, array $kinders): void
    {
        foreach ($kinders as $id => $kinder) {
            $serieId = $kinder->getSerie()->getId();
            if (!isset($series[$serieId])) {
                continue;
            }
            $series[$serieId]->kinders[$id] = $kinder;
        }

        $sortKinders = static function (KinderPresenter $a, KinderPresenter $b) {
            foreach (Serie::KINDER_SORTING as $field => $order) {
                $asc = 'ASC' === strtoupper($order);
                $res = $a->$field() <=> $b->$field();
                if (0 !== $res) {
                    return $asc ? $res : -$res;
                }
            }

            return 0;
        };

        foreach ($series as $serie) {
            uasort($serie->kinders, $sortKinders);
        }
    }

    /**
     * @return array<int, Country>
     */
    private function queryCountries(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Country::class, 'e', 'e.id')
            ->getQuery()->getResult();
    }

    /**
     * @param int[] $kinderIds
     *
     * @return array<int, Kinder>
     */
    private function queryKinderImages(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        // Kinders en direct indexés par ID
        $qb1 = $this->entityManager->createQueryBuilder()
            ->select('e, i')
            ->from(Kinder::class, 'e', 'e.id')
            ->join('e.images', 'i')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', $kinderIds);

        foreach (Serie::KINDER_SORTING as $field => $order) {
            $qb1->addOrderBy("e.$field", $order);
        }

        // Kinders originaux (non-virtuels) indexés par ID
        $qb2 = $this->entityManager->createQueryBuilder()
            ->select('e, o, i')
            ->from(Kinder::class, 'e', 'e.id')
            ->join('e.original', 'o')
            ->join('o.images', 'i')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', $kinderIds);

        foreach (Serie::KINDER_SORTING as $field => $order) {
            $qb2->addOrderBy("e.$field", $order);
        }

        // le + est voulu -> ajoute les id manquants
        return $qb1->getQuery()->getResult() + $qb2->getQuery()->getResult();
    }

    /**
     * @param int[] $serieIds
     *
     * @return array<int, Kinder>
     */
    public function queryAllKinders(array $serieIds): array
    {
        if (!$serieIds) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Kinder::class, 'e', 'e.id')
            ->where('e.serie IN (:ids)')
            ->setParameter('ids', $serieIds);

        foreach (Serie::KINDER_SORTING as $field => $order) {
            $qb->addOrderBy("e.$field", $order);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int[] $kinderIds
     *
     * @return array<int, ZBA>
     */
    private function queryZbas(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(ZBA::class, 'e', 'e.id')
            ->where('e.kinder IN (:ids)')
            ->setParameter('ids', $kinderIds)
            ->getQuery()->getResult();
    }

    /**
     * @param int[] $kinderIds
     *
     * @return array<int, BPZ>
     */
    private function queryBpzs(array $kinderIds): array
    {
        if (!$kinderIds) {
            return [];
        }

        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(BPZ::class, 'e', 'e.id')
            ->where('e.kinder IN (:ids)')
            ->setParameter('ids', $kinderIds)
            ->getQuery()->getResult();
    }
}
