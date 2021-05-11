<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Front;

use App\Repository\CollectionRepository;
use App\Repository\MenuItemRepository;
use App\Repository\SerieRepository;
use App\Service\Front\PageDoubles;
use App\Service\Front\PageLookingFor;
use App\Service\Front\PageSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="front_home")
     */
    public function home()
    {
        $context = [];

        return $this->render('index.html.twig', $context);
    }

    /**
     * @Route("/looking-for", name="front_lookingfor")
     */
    public function recherches(PageLookingFor $lookingFor)
    {
        $context = [
            'series' => $lookingFor->getSeries(),
        ];

        return $this->render('looking-for.html.twig', $context);
    }

    /**
     * @Route("/doubles", name="front_doubles")
     */
    public function doubles(PageDoubles $frontDoubles)
    {
        $context = [
            'series' => $frontDoubles->getSeries(),
        ];

        return $this->render('doubles.html.twig', $context);
    }

    /**
     * @Route("/serie/{id}-{slug}", name="front_serie", requirements={"id": "\d+"})
     * @Route("/serie/{id}", requirements={"id": "\d+"})
     */
    public function serie(SerieRepository $repository, int $id, string $slug = '')
    {
        $serie = $repository->find($id);
        if (!$serie) {
            throw new ResourceNotFoundException();
        }
        if ($serie->getSlug() && $serie->getSlug() !== $slug) {
            return $this->redirectToRoute('front_serie', ['id' => $serie->getId(), 'slug' => $serie->getSlug()]);
        }

        $context = [
            'serie' => $serie,
        ];

        return $this->render('serie.html.twig', $context);
    }

    /**
     * @Route("/collection/{id}-{slug}", name="front_collection", requirements={"id": "\d+"})
     */
    public function collection(CollectionRepository $repository, int $id, string $slug = '')
    {
        $collection = $repository->find($id);
        if (!$collection) {
            throw new ResourceNotFoundException();
        }
        if ($collection->getSlug() && $collection->getSlug() !== $slug) {
            return $this->redirectToRoute('front_collection', ['id' => $collection->getId(), 'slug' => $collection->getSlug()]);
        }
        if (1 === $collection->getSeries()->count()) {
            $serie = $collection->getSeries()->get(0);

            return $this->redirectToRoute('front_serie', ['id' => $serie->getId(), 'slug' => $serie->getSlug()]);
        }

        $context = [
            'collection' => $collection,
        ];

        return $this->render('collection.html.twig', $context);
    }

    /**
     * @Route("/search/{id}-{slug}", name="front_search", requirements={"id": "\d+"})
     */
    public function search(PageSearch $frontSearch, MenuItemRepository $repository, int $id, string $slug = '')
    {
        $menuItem = $repository->find($id);
        if (!$menuItem) {
            throw new ResourceNotFoundException();
        }
        if ($menuItem->getSlug() && $menuItem->getSlug() !== $slug) {
            return $this->redirectToRoute('front_search', ['id' => $menuItem->getId(), 'slug' => $menuItem->getSlug()]);
        }

        $context = [
            'menuitem' => $menuItem,
            'collections' => $frontSearch->getSeriesByCollection($menuItem),
        ];

        return $this->render('search.html.twig', $context);
    }
}
