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
    public function index(Breadcrumb $breadcrumb, AttributeRepository $repository, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Attributs', $this->generateUrl('admin_attributes'));
        $searchFilter->setRouteName('admin_attributes');
        return $this->render('@admin/attributes/index.html.twig', [
            'items' => $repository->searchAll(),
        ]);
    }

    /**
     * @Route("/attributes/add", name="admin_attributes_add")
     */
    public function add(Breadcrumb $breadcrumb, FormFactory $formFactory)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Attributs', $this->generateUrl('admin_attributes'));
        $breadcrumb->add('Ajouter', $this->generateUrl('admin_attributes_add'));

        return $formFactory->render('@admin/attributes/form.html.twig', new Attribute(), 'Créer')
            ?: $this->redirectToRoute('admin_attributes');
    }

    /**
     * @Route("/attributes/edit-{id}", name="admin_attributes_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, AttributeRepository $repository, int $id)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Attributs', $this->generateUrl('admin_attributes'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_attributes_edit', ['id' => $id]));

        return $formFactory->render('@admin/attributes/form.html.twig', $repository->find($id), 'Modifier')
            ?: $this->redirectToRoute('admin_attributes');
    }
}
