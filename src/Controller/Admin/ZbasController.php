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

use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ZbasController extends AbstractController
{
    /**
     * @Route("/zbas/", name="admin_zbas")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('ZBAs', $this->generateUrl('admin_zbas'));
        $context = [];
        return $this->render('@admin/zbas/index.html.twig', $context);
    }
}
