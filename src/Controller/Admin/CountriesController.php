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
use App\Repository\CountryRepository;
use App\Service\Breadcrumb;
use App\Service\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CountriesController extends AbstractController
{
    /**
     * @Route("/countries/", name="admin_countries")
     */
    public function index(CountryRepository $repository, Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));
        return $this->render('@admin/countries/index.html.twig', [
            'items' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/countries/add", name="admin_countries_add")
     */
    public function add(FormFactory $formFactory, Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_countries_add'));

        $country = new Country();

        if (!($form = $formFactory->create($country))) {
            return $this->redirectToRoute('admin_countries');
        }

        return $this->render('@admin/countries/add.html.twig', [
            'item' => $country,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/countries/edit-{id}", name="admin_countries_edit", requirements={"id": "\d+"})
     */
    public function edit(FormFactory $formFactory, CountryRepository $repository, Breadcrumb $breadcrumb, int $id)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_countries'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_countries_edit', ['id' => $id]));

        if (!($country = $repository->find($id))) {
            return $this->redirectToRoute('admin_countries');
        }

        if (!($form = $formFactory->create($country))) {
            return $this->redirectToRoute('admin_countries');
        }

        return $this->render('@admin/countries/add.html.twig', [
            'item' => $country,
            'form' => $form->createView(),
        ]);
    }
}
