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

use App\Entity\BPZ;
use App\Form\AutocompleteImages;
use App\Form\FormFactory;
use App\Form\Type\BPZType;
use App\Repository\BPZRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BpzsController extends AbstractController
{
    /**
     * @Route("/bpzs/", name="admin_bpzs")
     */
    public function index(BPZRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $searchFilter->setRouteName('admin_bpzs');
        return $this->render('@admin/bpzs/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/bpzs/add", name="admin_bpzs_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_bpzs_add'));

        return $formFactory->render('@admin/bpzs/form.html.twig', new BPZ(), 'Créer')
            ?: $this->redirectToRoute('admin_bpzs');
    }

    /**
     * @Route("/bpzs/edit-{id}", name="admin_bpzs_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, BPZRepository $repository, int $id)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_bpzs_edit', ['id' => $id]));

        return $formFactory->render('@admin/bpzs/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_bpzs');
    }

    /**
     * @Route("/bpzs/autocomplete", name="admin_bpzs_autocomplete")
     */
    public function autocomplete(AutocompleteImages $autocomplete, Request $request)
    {
        $result = $autocomplete->getResult($request, BPZType::class);
        return new JsonResponse($result);
    }
}
