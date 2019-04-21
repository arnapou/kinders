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

use App\Entity\BaseItem;
use Doctrine\Common\Persistence\ManagerRegistry;

class ImageTypeGuesser
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getTypes(): array
    {
        $types = [];
        $em    = $this->doctrine->getManager();
        $meta  = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $reflectionClass = new \ReflectionClass($m->getName());
            if ($reflectionClass->isInstantiable() && $reflectionClass->isSubclassOf(BaseItem::class)) {
                $types[] = $reflectionClass->getShortName();
            }
        }
        return array_combine($types, $types);
    }

    public static function guess($class): string
    {
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->getShortName();
    }
}
