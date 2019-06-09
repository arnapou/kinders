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

use App\Entity\MenuItem;
use App\Form\FormFactory;
use App\Repository\MenuItemRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MenuItemsController extends AbstractController
{
    /**
     * @Route("/menuitems/", name="admin_menuitems")
     */
    public function index(MenuItemRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Pays', $this->generateUrl('admin_menuitems'));
        return $this->render('@admin/menuitems/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/menuitems/add", name="admin_menuitems_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Menu catégorie', $this->generateUrl('admin_menuitems'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_menuitems_add'));

        return $formFactory->renderAdd('@admin/menuitems/form.html.twig', new MenuItem())
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/menuitems/edit-{id}", name="admin_menuitems_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, MenuItemRepository $repository, int $id)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Menu catégorie', $this->generateUrl('admin_menuitems'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_menuitems_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/menuitems/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/menuitems/delete-{id}", name="admin_menuitems_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, MenuItemRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_menuitems');
    }
}
