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

use Symfony\Component\DependencyInjection\ContainerInterface;

class Pagination implements \IteratorAggregate
{
    const MAX_PAGES = 10;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var int
     */
    private $pageNum = null;
    /**
     * @var int
     */
    private $pageSize = 15;
    /**
     * @var int
     */
    private $pageCount = 1;
    /**
     * @var int
     */
    private $itemCount = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setItemCount(int $count)
    {
        $this->itemCount = $count;
        $this->pageCount = ceil($count / $this->pageSize);
    }

    public function getPageNum(): int
    {
        if (null == $this->pageNum) {
            $request   = $this->container->get('request_stack')->getCurrentRequest();
            $num       = $request->get('page');
            $routeName = $request->get('_route');
            if ($num && ctype_digit("$num")) {
                $request->getSession()->set("page.$routeName", \intval($num));
            } else {
                $num = $request->getSession()->get("page.$routeName");
            }
            if ($num < 1) {
                $num = 1;
            }
            if ($num > $this->pageCount) {
                $num = $this->pageCount;
            }
            $this->pageNum = $num;
        }
        return $this->pageNum;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    public function offsetStart(): int
    {
        $offset = ($this->getPageNum() - 1) * $this->getPageSize();
        return $offset < 0 ? 0 : $offset;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getPages());
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        $page  = $this->getPageNum();
        $count = $this->getPageCount();

        if ($count > self::MAX_PAGES) {
            $half  = floor(self::MAX_PAGES / 2);
            $pages = range($page - $half + 1, $page + $half);
            if ($pages[0] < 1) {
                $val = 1 - $pages[0];
                foreach ($pages as &$page) {
                    $page += $val;
                }
            }
            if ($pages[\count($pages) - 1] >= $count) {
                $val = $pages[\count($pages) - 1] - $count;
                foreach ($pages as &$page) {
                    $page -= $val;
                }
            }
            $pages = array_unique(array_merge($pages, [1, $count]));
            sort($pages);
            $newPages = [$pages[0]];
            for ($i = 1; $i < \count($pages); $i++) {
                if ($pages[$i] - $pages[$i - 1] > 1) {
                    $newPages[] = 0;
                }
                $newPages[] = $pages[$i];
            }
            return $newPages;
        } else {
            return range(1, $count);
        }
    }
}