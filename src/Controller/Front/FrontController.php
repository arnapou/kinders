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

use App\Repository\MenuItemRepository;
use App\Repository\SerieRepository;
use App\Service\FrontSearch;
use App\Service\FrontSerie;
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
     * @Route("/serie/{id}-{slug}", name="front_serie", requirements={"id": "\d+"})
     * @Route("/serie/{id}", requirements={"id": "\d+"})
     */
    public function serie(FrontSerie $frontSerie, SerieRepository $repository, int $id, string $slug = '')
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
            'refs'  => $frontSerie->getKinderReferences($serie),
        ];
        return $this->render('serie.html.twig', $context);
    }

    /**
     * @Route("/search/{id}-{slug}", name="front_search", requirements={"id": "\d+"})
     */
    public function search(FrontSearch $frontSearch, MenuItemRepository $repository, int $id, string $slug = '')
    {
        $item = $repository->find($id);
        if (!$item) {
            throw new ResourceNotFoundException();
        }
        if ($item->getSlug() && $item->getSlug() !== $slug) {
            return $this->redirectToRoute('front_search', ['id' => $item->getId(), 'slug' => $item->getSlug()]);
        }

        $context = [
            'menuitem' => $item,
            'series'   => $frontSearch->getSeries($item),
        ];
        return $this->render('search.html.twig', $context);
    }
}
