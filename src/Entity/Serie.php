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
 * @ORM\Entity(repositoryClass="App\Repository\SerieRepository")
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
class Serie extends BaseItem
{
    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Kinder", mappedBy="serie", orphanRemoval=true)
     */
    private $kinders;

    public function __construct()
    {
        parent::__construct();
        $this->kinders = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return Collection|Kinder[]
     */
    public function getKinders(): Collection
    {
        return $this->kinders;
    }

    public function addKinder(Kinder $kinder): self
    {
        if (!$this->kinders->contains($kinder)) {
            $this->kinders[] = $kinder;
            $kinder->setSerie($this);
        }

        return $this;
    }

    public function removeKinder(Kinder $kinder): self
    {
        if ($this->kinders->contains($kinder)) {
            $this->kinders->removeElement($kinder);
            // set the owning side to null (unless already changed)
            if ($kinder->getSerie() === $this) {
                $kinder->setSerie(null);
            }
        }

        return $this;
    }
}
