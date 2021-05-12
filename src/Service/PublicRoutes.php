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
use Symfony\Component\Routing\Route;

class PublicRoutes
{
    /**
     * @var array<string, Route>
     */
    private ?array $routes = null;

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @return array<string, string>
     */
    public function names(): array
    {
        $keys = array_keys($this->routes());

        return array_combine($keys, $keys);
    }

    /**
     * @return array<string, Route>
     */
    public function routes(): array
    {
        if (null === $this->routes) {
            $this->routes = [];
            foreach ($this->container->get('router')->getRouteCollection()->all() as $name => $route) {
                if (!str_starts_with($name, 'front')) {
                    continue;
                }

                if ($variables = $route->compile()->getVariables()) {
                    foreach ($variables as $variable) {
                        if (!isset($route->getDefaults()[$variable])) {
                            continue 2;
                        }
                    }
                }

                $this->routes[$name] = $route;
            }
        }

        return $this->routes;
    }
}
