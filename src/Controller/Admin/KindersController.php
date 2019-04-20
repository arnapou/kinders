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

class KindersController extends AbstractController
{
    /**
     * @Route("/kinders/", name="admin_kinders")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Kinders', $this->generateUrl('admin_kinders'));
        $context = [];
        return $this->render('@admin/kinders/index.html.twig', $context);
    }
}
