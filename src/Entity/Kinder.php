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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KinderRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="name", columns={"name"}),
 *     @ORM\Index(name="quantity_owned", columns={"quantity_owned"}),
 *     @ORM\Index(name="quantity_double", columns={"quantity_double"}),
 *     @ORM\Index(name="reference", columns={"reference"}),
 *     @ORM\Index(name="looking_for", columns={"looking_for"}),
 *     @ORM\Index(name="year", columns={"year"}),
 * })
 */
class Kinder extends BaseItem
{
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\BPZ", mappedBy="kinder", orphanRemoval=true)
     */
    private $bpzs;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ZBA", mappedBy="kinder", orphanRemoval=true)
     */
    private $zbas;

    /**
     * @var Serie
     * @ORM\ManyToOne(targetEntity="App\Entity\Serie", inversedBy="kinders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    public function __construct()
    {
        parent::__construct();
        $this->bpzs = new ArrayCollection();
        $this->zbas = new ArrayCollection();
    }

    /**
     * @return Collection|BPZ[]
     */
    public function getBpzs(): Collection
    {
        return $this->bpzs;
    }

    public function addBpz(BPZ $bpz): self
    {
        if (!$this->bpzs->contains($bpz)) {
            $this->bpzs[] = $bpz;
            $bpz->setKinder($this);
        }
        return $this;
    }

    public function removeBpz(BPZ $bpz): self
    {
        if ($this->bpzs->contains($bpz)) {
            $this->bpzs->removeElement($bpz);
            // set the owning side to null (unless already changed)
            if ($bpz->getKinder() === $this) {
                $bpz->setKinder(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|ZBA[]
     */
    public function getZbas(): Collection
    {
        return $this->zbas;
    }

    public function addZba(ZBA $zba): self
    {
        if (!$this->zbas->contains($zba)) {
            $this->zbas[] = $zba;
            $zba->setKinder($this);
        }
        return $this;
    }

    public function removeZba(ZBA $zba): self
    {
        if ($this->zbas->contains($zba)) {
            $this->zbas->removeElement($zba);
            // set the owning side to null (unless already changed)
            if ($zba->getKinder() === $this) {
                $zba->setKinder(null);
            }
        }
        return $this;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setSerie(?Serie $serie): self
    {
        $this->serie = $serie;
        return $this;
    }
}
