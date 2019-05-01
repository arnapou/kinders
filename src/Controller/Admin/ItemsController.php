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

use App\Entity\Item;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\ItemType;
use App\Repository\ItemRepository;
use App\Repository\SerieRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ItemsController extends AbstractController
{
    /**
     * @Route("/items/", name="admin_items")
     */
    public function index(ItemRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $searchFilter->setRouteName('admin_items');
        return $this->render('@admin/items/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/items/add", name="admin_items_add")
     * @Route("/items/add-{id}", name="admin_items_add_parent", requirements={"id": "\d+"})
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory, ?int $id, SerieRepository $serieRepository)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_items_add'));

        $entity = (new Item())->setSerie($serieRepository->find(\intval($id)));

        return $formFactory->renderAdd('@admin/items/form.html.twig', $entity)
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/items/edit-{id}", name="admin_items_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, ItemRepository $repository, int $id)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_items_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/items/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/items/delete-{id}", name="admin_items_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, ItemRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_items');
    }

    /**
     * @Route("/items/autocomplete", name="admin_items_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        if ('images' === $request->get('field_name')) {
            $result = $autocomplete->images($request, ItemType::class);
        } else {
            $result = $autocomplete->entities($request, ItemType::class);
        }
        return new JsonResponse($result);
    }
}
