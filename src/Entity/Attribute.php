<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttributeRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="slug", columns={"slug"}),
 *     @ORM\Index(name="name", columns={"name"}),
 *     @ORM\Index(name="type", columns={"type"}),
 * })
 */
class Attribute extends BaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $type = '';

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
