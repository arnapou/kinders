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

use App\Entity\Piece;
use App\Form\AutocompleteService;
use App\Form\FormFactory;
use App\Form\Type\Entity\PieceType;
use App\Repository\PieceRepository;
use App\Repository\SerieRepository;
use App\Service\Breadcrumb;
use App\Service\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PiecesController extends AbstractController
{
    /**
     * @Route("/pieces/", name="admin_pieces")
     */
    public function index(PieceRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Pièces', $this->generateUrl('admin_pieces'));
        $searchFilter->setRouteName('admin_pieces');
        return $this->render('@admin/pieces/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/pieces/add", name="admin_pieces_add")
     * @Route("/pieces/add-{id}", name="admin_pieces_add_parent", requirements={"id": "\d+"})
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory, ?int $id, SerieRepository $serieRepository)
    {
        $breadcrumb->add('Pièces', $this->generateUrl('admin_pieces'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_pieces_add'));

        $entity = (new Piece())->setSerie($serieRepository->find(\intval($id)));

        return $formFactory->renderAdd('@admin/pieces/form.html.twig', $entity)
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/pieces/edit-{id}", name="admin_pieces_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, PieceRepository $repository, int $id)
    {
        $breadcrumb->add('Pièces', $this->generateUrl('admin_pieces'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_pieces_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/pieces/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/pieces/delete-{id}", name="admin_pieces_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, PieceRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_pieces');
    }

    /**
     * @Route("/pieces/autocomplete", name="admin_pieces_autocomplete")
     */
    public function autocomplete(AutocompleteService $autocomplete, Request $request)
    {
        if ('images' === $request->get('field_name')) {
            $result = $autocomplete->images($request, PieceType::class);
        } else {
            $result = $autocomplete->entities($request, PieceType::class);
        }
        return new JsonResponse($result);
    }
}
