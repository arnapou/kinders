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
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\BPZType;
use App\Repository\BPZRepository;
use App\Repository\KinderRepository;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
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
     * @Route("/bpzs/add-{id}", name="admin_bpzs_add_parent", requirements={"id": "\d+"})
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory, ?int $id, KinderRepository $kinderRepository)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_bpzs_add'));

        $entity = (new BPZ())->setKinder($kinderRepository->find(\intval($id)));

        return $formFactory->renderAdd('@admin/bpzs/form.html.twig', $entity)
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/bpzs/edit-{id}", name="admin_bpzs_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, BPZRepository $repository, int $id)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_bpzs_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/bpzs/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/bpzs/delete-{id}", name="admin_bpzs_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, BPZRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_bpzs');
    }

    /**
     * @Route("/bpzs/autocomplete", name="admin_bpzs_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        if ('images' === $request->get('field_name')) {
            $result = $autocomplete->images($request, BPZType::class);
        } else {
            $result = $autocomplete->entities($request, BPZType::class);
        }

        return new JsonResponse($result);
    }
}
