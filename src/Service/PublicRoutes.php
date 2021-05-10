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
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Route[]
     */
    private $routes = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function names(): array
    {
        $keys = array_keys($this->routes());

        return array_combine($keys, $keys);
    }

    /**
     * @return Route[]
     */
    public function routes(): array
    {
        if (null === $this->routes) {
            $this->routes = [];
            foreach ($this->container->get('router')->getRouteCollection()->all() as $name => $route) {
                if (0 === strpos($name, 'front')) {
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
        }

        return $this->routes;
    }
}
