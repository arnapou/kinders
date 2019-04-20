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

class ItemsController extends AbstractController
{
    /**
     * @Route("/items/", name="admin_items")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Items', $this->generateUrl('admin_items'));
        $context = [];
        return $this->render('@admin/items/index.html.twig', $context);
    }
}
