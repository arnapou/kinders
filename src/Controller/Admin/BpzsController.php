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

class BpzsController extends AbstractController
{
    /**
     * @Route("/bpzs/", name="admin_bpzs")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('BPZs', $this->generateUrl('admin_bpzs'));
        $context = [];
        return $this->render('@admin/bpzs/index.html.twig', $context);
    }
}
