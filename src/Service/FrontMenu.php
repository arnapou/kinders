<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\MenuCategory;
use App\Repository\MenuCategoryRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontMenu
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var MenuCategoryRepository
     */
    private $repository;
    /**
     * @var PublicRoutes
     */
    private $publicRoutes;

    public function __construct(UrlGeneratorInterface $urlGenerator, MenuCategoryRepository $repository, PublicRoutes $publicRoutes)
    {
        $this->repository   = $repository;
        $this->urlGenerator = $urlGenerator;
        $this->publicRoutes = $publicRoutes;
    }

    public function getCategories(bool $sidebar = true): array
    {
        $categories = [];
        foreach ($this->repository->findBy(['sidebar' => $sidebar]) as $category) {
            $array = $this->getCategory($category);
            if (!empty($array['items'])) {
                $categories[] = $array;
            }
        }
        return $categories;
    }

    private function getCategory(MenuCategory $category): array
    {
        $publicRouteNames = $this->publicRoutes->names();
        $items            = [];
        foreach ($category->getItems() as $item) {
            if ($item->getRouteName()) {
                if (!isset($publicRouteNames[$item->getRouteName()])) {
                    continue;
                }
                $url = $this->urlGenerator->generate($item->getRouteName());
            } else {
                $url = $this->urlGenerator->generate('front_search', ['id' => $item->getId(), 'slug' => $item->getSlug()]);
            }
            $items[] = [
                'name' => $item->getName(),
                'url'  => $url,
            ];
        }
        return [
            'name'  => $category->getName(),
            'items' => $items,
        ];
    }
}
