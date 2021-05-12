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

use App\Entity\Image;
use App\Repository\CollectionRepository;
use App\Repository\MenuItemRepository;
use App\Repository\SerieRepository;
use App\Service\Front\PageDoubles;
use App\Service\Front\PageLastModified;
use App\Service\Front\PageLookingFor;
use App\Service\Front\PageRandom;
use App\Service\Front\PageSearch;
use App\Service\ImageHelper;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @Route("/random-serie", name="front_random_serie")
     */
    public function randomSerie(PageRandom $pageRandom)
    {
        $serie = $pageRandom->getRandomSerie() ?? throw new ResourceNotFoundException();

        return $this->redirectToRoute('front_serie', $serie->routeParameters());
    }

    /**
     * @Route("/random-kinder-image", name="random_kinder_image")
     */
    public function randomKinderImage(PageRandom $pageRandom, ImageHelper $helper, int $size = 250)
    {
        $image = $pageRandom->getRandomKinderImage();
        $parameters = $helper->thumbnailRouteParameters($image);
        if (empty($parameters)) {
            return $this->redirect('/en-construction.png');
        }

        return $this->redirectToRoute('image_thumbnail_wh', $parameters + ['w' => $size, 'h' => $size]);
    }

    /**
     * @Route("/looking-for", name="front_lookingfor")
     * @Route("/looking-for.pdf", name="front_lookingfor_pdf", defaults={"pdf": true})
     */
    public function mesRecherches(PageLookingFor $pageLookingFor, MenuItemRepository $repository, bool $pdf = false)
    {
        $context = [
            'menuitem' => $repository->findOneByRouteName('front_lookingfor'),
            'series' => $pageLookingFor->getSeries(),
            'link_pdf' => $this->generateUrl('front_lookingfor_pdf', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if (!$pdf) {
            return $this->render('listing.html.twig', $context);
        }

        $this->renderInlinePdf('listing.pdf.twig', 'arnapou_recherches.pdf', $context);
    }

    /**
     * @Route("/doubles", name="front_doubles")
     * @Route("/doubles.pdf", name="front_doubles_pdf", defaults={"pdf": true})
     */
    public function mesDoubles(PageDoubles $pageDoubles, MenuItemRepository $repository, bool $pdf = false)
    {
        $context = [
            'menuitem' => $repository->findOneByRouteName('front_doubles'),
            'series' => $pageDoubles->getSeries(),
            'link_pdf' => $this->generateUrl('front_doubles_pdf', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if (!$pdf) {
            return $this->render('listing.html.twig', $context);
        }

        $this->renderInlinePdf('listing.pdf.twig', 'arnapou_doubles.pdf', $context);
    }

    /**
     * @Route("/serie/{id}-{slug}", name="front_serie", requirements={"id": "\d+"})
     * @Route("/serie/{id}", requirements={"id": "\d+"})
     */
    public function serie(SerieRepository $repository, int $id, string $slug = '')
    {
        $serie = $repository->find($id) ?? throw new ResourceNotFoundException();

        if ($serie->getSlug() && $serie->getSlug() !== $slug) {
            return $this->redirectToRoute('front_serie', $serie->routeParameters());
        }

        $context = ['serie' => $serie];

        return $this->render('serie.html.twig', $context);
    }

    /**
     * @Route("/collection/{id}-{slug}", name="front_collection", requirements={"id": "\d+"})
     */
    public function collection(CollectionRepository $repository, int $id, string $slug = '')
    {
        $collection = $repository->find($id) ?? throw new ResourceNotFoundException();

        if ($collection->getSlug() && $collection->getSlug() !== $slug) {
            return $this->redirectToRoute('front_collection', $collection->routeParameters());
        }

        if (1 === $collection->getSeries()->count()) {
            $serie = $collection->getSeries()->get(0);

            return $this->redirectToRoute('front_serie', $serie->routeParameters());
        }

        $context = ['collection' => $collection];

        return $this->render('collection.html.twig', $context);
    }

    /**
     * @Route("/last-modified", name="front_last_modified")
     */
    public function lastModified(PageLastModified $pageLastModified, MenuItemRepository $repository, Request $request)
    {
        $context = [
            'menuitem' => $repository->findOneByRouteName($request->get('_route')),
            'collections' => $pageLastModified->getSeriesByCollection(null),
        ];

        return $this->render('search.html.twig', $context);
    }

    /**
     * @Route("/search/{id}-{slug}", name="front_search", requirements={"id": "\d+"})
     */
    public function search(PageSearch $frontSearch, MenuItemRepository $repository, int $id, string $slug = '')
    {
        $menuItem = $repository->find($id) ?? throw new ResourceNotFoundException();

        if ($menuItem->getSlug() && $menuItem->getSlug() !== $slug) {
            return $this->redirectToRoute('front_search', $menuItem->routeParameters());
        }

        $context = [
            'menuitem' => $menuItem,
            'collections' => $frontSearch->getSeriesByCollection($menuItem),
        ];

        return $this->render('search.html.twig', $context);
    }

    /**
     * @return no-return
     */
    private function renderInlinePdf(string $view, string $filename, array $context): void
    {
        $urlHome = $this->generateUrl('front_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $context += [
            'pdf_filename' => $filename,
            'link_home' => $urlHome,
            'domain_home' => parse_url($urlHome)['host'] ?? null,
        ];

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($this->renderView($view, $context));
        $mpdf->Output($filename, Destination::INLINE);
        exit;
    }
}
