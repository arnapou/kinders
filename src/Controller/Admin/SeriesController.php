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

use App\Entity\Serie;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\SerieType;
use App\Repository\SerieRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    /**
     * @Route("/series/", name="admin_series")
     */
    public function index(SerieRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Séries', $this->generateUrl('admin_series'));
        $searchFilter->setRouteName('admin_series');
        return $this->render('@admin/series/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/series/add", name="admin_series_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Séries', $this->generateUrl('admin_series'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_series_add'));

        return $formFactory->render('@admin/series/form.html.twig', new Serie(), 'Créer')
            ?: $this->redirectToRoute('admin_series');
    }

    /**
     * @Route("/series/edit-{id}", name="admin_series_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, SerieRepository $repository, int $id)
    {
        $breadcrumb->add('Séries', $this->generateUrl('admin_series'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_series_edit', ['id' => $id]));

        return $formFactory->render('@admin/series/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_series');
    }

    /**
     * @Route("/series/delete-{id}", name="admin_series_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, SerieRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_series');
    }

    /**
     * @Route("/series/autocomplete", name="admin_series_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        $result = $autocomplete->images($request, SerieType::class);
        return new JsonResponse($result);
    }

    /**
     * @Route("/series/view-{id}", name="admin_series_view", requirements={"id": "\d+"})
     */
    public function view(Breadcrumb $breadcrumb, FormFactory $formFactory, SerieRepository $repository, int $id)
    {
        $breadcrumb->add('Séries', $this->generateUrl('admin_series'));
        $breadcrumb->add('Voir', $this->generateUrl('admin_series_view', ['id' => $id]));

        $serie = $repository->find($id);

        return $serie
            ? $this->render('@admin/series/view.html.twig', ['serie' => $serie])
            : $this->redirectToRoute('admin_series');
    }
}
