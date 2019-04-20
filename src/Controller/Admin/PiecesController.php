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

class PiecesController extends AbstractController
{
    /**
     * @Route("/pieces/", name="admin_pieces")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Pièces', $this->generateUrl('admin_pieces'));
        $context = [];
        return $this->render('@admin/pieces/index.html.twig', $context);
    }
}
