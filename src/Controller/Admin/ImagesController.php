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
use App\Form\Type\Multiple\ImagesUploadType;
use App\Repository\ImageRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function add(
        Breadcrumb $breadcrumb,
        Request $request,
        EntityManagerInterface $entityManager,
        ImageRepository $imageRepository,
        ?string $type
    ) {
        $breadcrumb->add('Images', $this->generateUrl('admin_images'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_images_add'));

        $data = ['type' => \in_array($type, $imageRepository->getTypes()) ? $type : ''];

        $form = $this->container->get('form.factory')->create(ImagesUploadType::class, $data, [
            'image_type' => $data['type'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!\in_array($data['type'], $imageRepository->getTypes())) {
                throw new \InvalidArgumentException('Not allowed image type');
            }
            for ($n = 1; $n <= ImagesUploadType::NB_IMAGES; $n++) {
                /** @var Image $image */
                if ($image = $data["image$n"]) {
                    $image->setType($data['type']);
                    $entityManager->persist($image);
                }
            }
            $entityManager->flush();
            return $this->redirect($breadcrumb->previous());
        }

        return $this->render('@admin/images/add.html.twig', [
            'form'     => $form->createView(),
            'nbimages' => ImagesUploadType::NB_IMAGES,
        ]);
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

        return $formFactory->renderEdit('@admin/images/edit.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }
}
