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
 * @ORM\Entity(repositoryClass="App\Repository\BPZRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="realsorting", columns={"realsorting"}),
 *     @ORM\Index(name="slug", columns={"slug"}),
 *     @ORM\Index(name="name", columns={"name"}),
 *     @ORM\Index(name="quantity_owned", columns={"quantity_owned"}),
 *     @ORM\Index(name="quantity_double", columns={"quantity_double"}),
 *     @ORM\Index(name="reference", columns={"reference"}),
 *     @ORM\Index(name="looking_for", columns={"looking_for"}),
 *     @ORM\Index(name="year", columns={"year"}),
 * })
 */
class BPZ extends BaseItem
{
    /**
     * @var Kinder
     * @ORM\ManyToOne(targetEntity="App\Entity\Kinder", inversedBy="bpzs", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $kinder;

    public function getKinder(): ?Kinder
    {
        return $this->kinder;
    }

    public function setKinder(?Kinder $kinder): self
    {
        $this->kinder = $kinder;
        return $this;
    }
}
