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

class ImagesController extends AbstractController
{
    /**
     * @Route("/images/", name="admin_images")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Images', $this->generateUrl('admin_images'));
        $context = [];
        return $this->render('@admin/images/index.html.twig', $context);
    }
}
