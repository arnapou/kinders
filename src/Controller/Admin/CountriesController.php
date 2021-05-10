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

use App\Entity\Country;
use App\Form\FormFactory;
use App\Repository\CountryRepository;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\SearchFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CountriesController extends AbstractController
{
    /**
     * @Route("/countries/", name="admin_countries")
     */
    public function index(CountryRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));

        return $this->render('@admin/countries/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/countries/add", name="admin_countries_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_countries_add'));

        return $formFactory->renderAdd('@admin/countries/form.html.twig', new Country())
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/countries/edit-{id}", name="admin_countries_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, CountryRepository $repository, int $id)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_countries_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/countries/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }

    /**
     * @Route("/countries/delete-{id}", name="admin_countries_delete", requirements={"id": "\d+"}, methods={"POST"})
     */
    public function delete(EntityManagerInterface $entityManager, CountryRepository $repository, int $id)
    {
        if ($item = $repository->find($id)) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_countries');
    }
}
