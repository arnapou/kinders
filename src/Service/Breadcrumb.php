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

class Breadcrumb implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array
     */
    private $items = [];
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Breadcrumb constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function add(string $label, string $url): void
    {
        $this->items[] = [
            'label' => $label,
            'url'   => $url,
        ];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function __toString(): string
    {
        return implode(' / ', array_map(function ($item) {
            return $item['label'];
        }, $this->items));
    }

    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    public function offsetUnset($offset)
    {
        // not implemented
    }

    public function previous()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($previous = $request->getSession()->get('last_route')) {
            return $this->container->get('router')->generate($previous['name'], $previous['params']);
        }
        return ($this[0] ?: $this[1] ?: ['url' => './'])['url'];
    }
}
