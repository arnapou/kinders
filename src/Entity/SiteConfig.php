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
 * @ORM\Entity(repositoryClass="App\Repository\MenuItemRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="slug", columns={"slug"}),
 *     @ORM\Index(name="name", columns={"name"}),
 * })
 */
class SiteConfig extends BaseEntity
{
    public const ID_HOME = 1;
}
