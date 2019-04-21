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

use App\Entity\Kinder;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\KinderType;
use App\Repository\KinderRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class KindersController extends AbstractController
{
    /**
     * @Route("/kinders/", name="admin_kinders")
     */
    public function index(KinderRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Kinders', $this->generateUrl('admin_kinders'));
        $searchFilter->setRouteName('admin_kinders');
        return $this->render('@admin/kinders/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/kinders/add", name="admin_kinders_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Kinders', $this->generateUrl('admin_kinders'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_kinders_add'));

        return $formFactory->render('@admin/kinders/form.html.twig', new Kinder(), 'Créer')
            ?: $this->redirectToRoute('admin_kinders');
    }

    /**
     * @Route("/kinders/edit-{id}", name="admin_kinders_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, KinderRepository $repository, int $id)
    {
        $breadcrumb->add('Kinders', $this->generateUrl('admin_kinders'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_kinders_edit', ['id' => $id]));

        return $formFactory->render('@admin/kinders/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_kinders');
    }

    /**
     * @Route("/kinders/delete-{id}", name="admin_kinders_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, KinderRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_kinders');
    }

    /**
     * @Route("/kinders/autocomplete", name="admin_kinders_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        if ('images' === $request->get('field_name')) {
            $result = $autocomplete->images($request, KinderType::class);
        } else {
            $result = $autocomplete->entities($request, KinderType::class);
        }
        return new JsonResponse($result);
    }
}
