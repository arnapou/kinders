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

use App\Entity\Attribute;
use App\Repository\AttributeRepository;
use App\Service\Breadcrumb;
use App\Service\FormFactory;
use App\Service\SearchFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AttributesController extends AbstractController
{
    /**
     * @Route("/attributes/", name="admin_attributes")
     */
    public function index(AttributeRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_attributes'));
        $searchFilter->setRouteName('admin_attributes');
        return $this->render('@admin/attributes/index.html.twig', [
            'items' => $repository->searchAll(),
        ]);
    }

    /**
     * @Route("/attributes/add", name="admin_attributes_add")
     */
    public function add(FormFactory $formFactory, Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_attributes'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_attributes_add'));

        $country = new Attribute();

        if (!($form = $formFactory->create($country))) {
            return $this->redirectToRoute('admin_attributes');
        }

        return $this->render('@admin/attributes/add.html.twig', [
            'item' => $country,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/attributes/edit-{id}", name="admin_attributes_edit", requirements={"id": "\d+"})
     */
    public function edit(FormFactory $formFactory, AttributeRepository $repository, Breadcrumb $breadcrumb, int $id)
    {
        $breadcrumb->add('Pays', $this->generateUrl('admin_attributes'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_attributes_edit', ['id' => $id]));

        if (!($country = $repository->find($id))) {
            return $this->redirectToRoute('admin_attributes');
        }

        if (!($form = $formFactory->create($country))) {
            return $this->redirectToRoute('admin_attributes');
        }

        return $this->render('@admin/attributes/add.html.twig', [
            'item' => $country,
            'form' => $form->createView(),
        ]);
    }
}
