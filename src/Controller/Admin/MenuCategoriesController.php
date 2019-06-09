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

use App\Entity\MenuCategory;
use App\Form\FormFactory;
use App\Repository\MenuCategoryRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MenuCategoriesController extends AbstractController
{
    /**
     * @Route("/menucategs/", name="admin_menucategs")
     */
    public function index(MenuCategoryRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Menu catégorie', $this->generateUrl('admin_menucategs'));
        return $this->render('@admin/menucategs/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/menucategs/add", name="admin_menucategs_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Menu catégorie', $this->generateUrl('admin_menucategs'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_menucategs_add'));

        return $formFactory->renderAdd('@admin/menucategs/form.html.twig', new MenuCategory())
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/menucategs/edit-{id}", name="admin_menucategs_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, MenuCategoryRepository $repository, int $id)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Menu catégorie', $this->generateUrl('admin_menucategs'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_menucategs_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/menucategs/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/menucategs/delete-{id}", name="admin_menucategs_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, MenuCategoryRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_menucategs');
    }
}
