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

class SearchFilter
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $values = [];
    /**
     * @var bool
     */
    private $visible = false;
    /**
     * @var string
     */
    private $defaultRouteName = '';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function value(?string $routeName = null): string
    {
        $routeName = $routeName ?: $this->defaultRouteName;

        if (!\array_key_exists($routeName, $this->values)) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
            $session = $request->getSession();

            $search = $request->get('search');
            if (null !== $search) {
                $session->set("search.$routeName", $search);
            } else {
                $search = $session->get("search.$routeName") ?: '';
            }

            $this->values[$routeName] = trim($search ?: '');
        }
        return $this->values[$routeName];
    }

    public function values(?string $routeName = null): array
    {
        $value = $this->value($routeName);
        if ($value !== '') {
            $value = preg_replace('!\s+!', ' ', $value);
            return explode(' ', $value);
        }
        return [];
    }

    public function isVisible(): bool
    {
        return $this->defaultRouteName ? true : false;
    }

    public function getRouteName(): string
    {
        return $this->defaultRouteName;
    }

    public function setRouteName(string $defaultRouteName): void
    {
        $this->defaultRouteName = $defaultRouteName;
    }
}
