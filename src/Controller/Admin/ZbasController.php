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

use App\Entity\ZBA;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\ZBAType;
use App\Repository\KinderRepository;
use App\Repository\ZBARepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ZbasController extends AbstractController
{
    /**
     * @Route("/zbas/", name="admin_zbas")
     */
    public function index(ZBARepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $searchFilter->setRouteName('admin_zbas');

        return $this->render('@admin/zbas/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/zbas/add", name="admin_zbas_add")
     * @Route("/zbas/add-{id}", name="admin_zbas_add_parent", requirements={"id": "\d+"})
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory, ?int $id, KinderRepository $kinderRepository)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_zbas_add'));

        $entity = (new ZBA())->setKinder($kinderRepository->find(\intval($id)));

        return $formFactory->renderAdd('@admin/zbas/form.html.twig', $entity)
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/zbas/edit-{id}", name="admin_zbas_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, ZBARepository $repository, int $id)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_zbas_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/zbas/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/zbas/delete-{id}", name="admin_zbas_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, ZBARepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_zbas');
    }

    /**
     * @Route("/zbas/autocomplete", name="admin_zbas_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        if ('images' === $request->get('field_name')) {
            $result = $autocomplete->images($request, ZBAType::class);
        } else {
            $result = $autocomplete->entities($request, ZBAType::class);
        }

        return new JsonResponse($result);
    }
}
