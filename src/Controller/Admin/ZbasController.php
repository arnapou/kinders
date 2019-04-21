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
use App\Form\AutocompleteImages;
use App\Form\FormFactory;
use App\Form\Type\ZBAType;
use App\Repository\ZBARepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
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
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_zbas_add'));

        return $formFactory->render('@admin/zbas/form.html.twig', new ZBA(), 'Créer')
            ?: $this->redirectToRoute('admin_zbas');
    }

    /**
     * @Route("/zbas/edit-{id}", name="admin_zbas_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, ZBARepository $repository, int $id)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_zbas_edit', ['id' => $id]));

        return $formFactory->render('@admin/zbas/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_zbas');
    }

    /**
     * @Route("/zbas/autocomplete", name="admin_zbas_autocomplete")
     */
    public function autocomplete(AutocompleteImages $autocomplete, Request $request)
    {
        $result = $autocomplete->getResult($request, ZBAType::class);
        return new JsonResponse($result);
    }
}
