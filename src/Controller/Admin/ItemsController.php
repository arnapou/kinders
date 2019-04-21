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
use App\Form\AutocompleteImages;
use App\Form\FormFactory;
use App\Form\Type\ItemType;
use App\Repository\ItemRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
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
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_items_add'));

        return $formFactory->render('@admin/items/form.html.twig', new Item(), 'Créer')
            ?: $this->redirectToRoute('admin_items');
    }

    /**
     * @Route("/items/edit-{id}", name="admin_items_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, ItemRepository $repository, int $id)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_items_edit', ['id' => $id]));

        return $formFactory->render('@admin/items/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_items');
    }

    /**
     * @Route("/items/autocomplete", name="admin_items_autocomplete")
     */
    public function autocomplete(AutocompleteImages $autocomplete, Request $request)
    {
        $result = $autocomplete->getResult($request, ItemType::class);
        return new JsonResponse($result);
    }
}
