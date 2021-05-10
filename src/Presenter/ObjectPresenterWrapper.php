<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Presenter;

use App\Entity\BaseEntity;

class ObjectPresenterWrapper
{
    private ?BaseEntity $object;

    public function __construct(?BaseEntity $object)
    {
        $this->object = $object;
    }

    public function __call(string $name, array $arguments)
    {
        if ($method = $this->findMethod($name)) {
            return $this->object->$method(...$arguments);
        }

        return null;
    }

    private function findMethod(string $name): string
    {
        if ($this->object) {
            foreach (['', 'get', 'is', 'has'] as $prefix) {
                if (method_exists($this->object, $prefix . $name)) {
                    return $prefix . $name;
                }
            }
        }

        return '';
    }

    public function __toString(): string
    {
        return (string) ($this->object ?: '');
    }
}
