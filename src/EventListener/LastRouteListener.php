<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

class LastRouteListener implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 0]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $routeName   = $request->get('_route');
        $routeParams = $request->get('_route_params');

        if ($session && !$this->isBlacklisted($routeName)) {
            $routeData = ['name' => $routeName, 'params' => $routeParams];

            // Do not save same matched route twice
            $thisRoute = $session->get('this_route', []);
            if ($thisRoute == $routeData) {
                return;
            }
            $session->set('last_route', $thisRoute);
            $session->set('this_route', $routeData);
        }
    }

    private function isBlacklisted($routeName): bool
    {
        return $routeName[0] === '_'
            || stripos($routeName, 'image_thumbnail') !== false
            || stripos($routeName, 'autocomplete') !== false
            || stripos($routeName, 'admin_') === false;
    }
}
