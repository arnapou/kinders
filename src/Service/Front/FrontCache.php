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

use Psr\Cache\CacheItemPoolInterface;

class FrontCache
{
    private array $memory = [];

    public function __construct(
        private CacheItemPoolInterface $cache
    ) {
    }

    public function from(string $key, callable $factory, int $ttl = 15)
    {
        if (\array_key_exists($key, $this->memory)) {
            return $this->memory[$key];
        }

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set($factory());
            $item->expiresAfter($ttl);
            $this->cache->save($item);
        }

        return $this->memory[$key] = $item->get();
    }
}
