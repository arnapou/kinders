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

use App\Form\FormFactory;
use App\Repository\SiteConfigRepository;
use App\Service\Admin\Breadcrumb;
use App\Service\Admin\SearchFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SiteConfigController extends AbstractController
{
    /**
     * @Route("/siteconfig/", name="admin_siteconfig")
     */
    public function index(SiteConfigRepository $repository, Breadcrumb $breadcrumb, SearchFilter $searchFilter)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Site', $this->generateUrl('admin_siteconfig'));

        return $this->render('@admin/siteconfig/index.html.twig', [
            'items' => $searchFilter->search($repository),
        ]);
    }

    /**
     * @Route("/siteconfig/edit-{id}", name="admin_siteconfig_edit", requirements={"id": "\d+"})
     */
    public function edit(Breadcrumb $breadcrumb, FormFactory $formFactory, SiteConfigRepository $repository, int $id)
    {
        $breadcrumb->add('Config', '');
        $breadcrumb->add('Site', $this->generateUrl('admin_siteconfig'));
        $breadcrumb->add('Modifier', $this->generateUrl('admin_siteconfig_edit', ['id' => $id]));

        return $formFactory->renderEdit('@admin/siteconfig/form.html.twig', $repository->find($id))
            ?: $this->redirect($breadcrumb->previous());
    }
}
