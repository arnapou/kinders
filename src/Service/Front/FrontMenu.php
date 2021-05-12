<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Front;

use App\Entity\MenuCategory;
use App\Entity\SiteConfig;
use App\Repository\MenuCategoryRepository;
use App\Repository\SiteConfigRepository;
use App\Service\PublicRoutes;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontMenu
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private MenuCategoryRepository $menuCategoryRepository,
        private SiteConfigRepository $siteConfigRepository,
        private PublicRoutes $publicRoutes
    ) {
    }

    public function getCategories(bool $sidebar = true): array
    {
        $categories = [];
        foreach ($this->menuCategoryRepository->findBy(['sidebar' => $sidebar]) as $category) {
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
        $items = [];
        foreach ($category->getItems() as $item) {
            if ($item->getRouteName()) {
                if (!isset($publicRouteNames[$item->getRouteName()])) {
                    continue;
                }
                $url = $this->urlGenerator->generate($item->getRouteName());
            } else {
                $url = $this->urlGenerator->generate('front_search', $item->routeParameters());
            }
            $items[] = [
                'name' => $item->getName(),
                'url' => $url,
            ];
        }

        return [
            'name' => $category->getName(),
            'items' => $items,
        ];
    }

    public function getConfigHome(): ?SiteConfig
    {
        return $this->siteConfigRepository->find(SiteConfig::ID_HOME);
    }
}
