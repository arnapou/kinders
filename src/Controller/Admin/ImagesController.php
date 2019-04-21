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

use App\Entity\Image;
use App\Form\FormFactory;
use App\Repository\ImageRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ImagesController extends AbstractController
{
    /**
     * @Route("/images/", name="admin_images")
     */
    public function index(Breadcrumb $breadcrumb, ImageRepository $repository, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Images', $this->generateUrl('admin_images'));
        $searchFilter->setRouteName('admin_images');
        return $this->render('@admin/images/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/images/add", name="admin_images_add")
     * @Route("/images/add-{type}", name="admin_images_add_type")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory, ?string $type = null, ImageRepository $repository)
    {
        $breadcrumb->add('Images', $this->generateUrl('admin_images'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_images_add'));

        $image = new Image();
        if ($type && \in_array($type, $repository->getTypes())) {
            $image->setType($type);
        }

        return $formFactory->render('@admin/images/form.html.twig', $image, 'Créer')
            ?: $this->redirectToRoute('admin_images');
    }

    /**
     * @Route("/images/delete-{id}", name="admin_images_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, ImageRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_images');
    }

    /**
     * @Route("/images/edit-{id}", name="admin_images_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, ImageRepository $repository, int $id)
    {
        $breadcrumb->add('Images', $this->generateUrl('admin_images'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_images_edit', ['id' => $id]));

        return $formFactory->render('@admin/images/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_images');
    }
}
