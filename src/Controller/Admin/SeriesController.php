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

class SeriesController extends AbstractController
{
    /**
     * @Route("/series/", name="admin_series")
     */
    public function index(Breadcrumb $breadcrumb)
    {
        $breadcrumb->add('Séries', $this->generateUrl('admin_series'));
        $context = [];
        return $this->render('@admin/series/index.html.twig', $context);
    }
}
