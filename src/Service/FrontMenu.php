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

    public function __construct(UrlGeneratorInterface $urlGenerator, MenuCategoryRepository $repository)
    {
        $this->repository   = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->repository->findAll() as $category) {
            $items = $category->getItems();
            if ($items->count()) {
                $categories[] = $this->getCategory($category);
            }
        }
        return $categories;
    }

    private function getCategory(MenuCategory $category): array
    {
        $items = [];
        foreach ($category->getItems() as $item) {
            $items[] = [
                'name' => $item->getName(),
                'url'  => $this->urlGenerator->generate('front_search', ['id' => $item->getId(), 'slug' => $item->getSlug()]),
            ];
        }
        return [
            'name'  => $category->getName(),
            'items' => $items,
        ];
    }
}
