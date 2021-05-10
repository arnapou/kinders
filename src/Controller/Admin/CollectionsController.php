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

use App\Entity\Collection;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\CollectionType;
use App\Repository\CollectionRepository;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CollectionsController extends AbstractController
{
    /**
     * @Route("/collections/", name="admin_collections")
     */
    public function index(CollectionRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Collections', $this->generateUrl('admin_collections'));
        $searchFilter->setRouteName('admin_collections');

        return $this->render('@admin/collections/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/collections/add", name="admin_collections_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Collections', $this->generateUrl('admin_collections'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_collections_add'));

        return $formFactory->renderAdd('@admin/collections/form.html.twig', new Collection())
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/collections/edit-{id}", name="admin_collections_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, CollectionRepository $repository, int $id)
    {
        $breadcrumb->add('Collections', $this->generateUrl('admin_collections'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_collections_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/collections/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/collections/delete-{id}", name="admin_collections_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, CollectionRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_collections');
    }

    /**
     * @Route("/collections/autocomplete", name="admin_collections_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        $result = $autocomplete->entities($request, CollectionType::class);

        return new JsonResponse($result);
    }
}
