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

use App\Exception\KinderVirtualException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KinderRepository")
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
class Kinder extends BaseItem
{
    public const BPZS_SORTING = ['name' => 'ASC'];
    public const ZBAS_SORTING = ['name' => 'ASC'];
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\BPZ", mappedBy="kinder", orphanRemoval=true, fetch="EAGER")
     */
    private $bpzs;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ZBA", mappedBy="kinder", orphanRemoval=true, fetch="EAGER")
     */
    private $zbas;

    /**
     * @var Serie
     * @ORM\ManyToOne(targetEntity="App\Entity\Serie", inversedBy="kinders", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    /**
     * @var self
     * @ORM\ManyToOne(targetEntity="App\Entity\Kinder", inversedBy="virtuals", fetch="EAGER")
     */
    private $original;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Kinder", mappedBy="original")
     */
    private $virtuals;

    public function __construct()
    {
        parent::__construct();
        $this->bpzs = new ArrayCollection();
        $this->zbas = new ArrayCollection();
        $this->virtuals = new ArrayCollection();
    }

    /**
     * @return Collection|BPZ[]
     */
    public function getBpzs(): Collection
    {
        $criteria = Criteria::create()->orderBy(self::BPZS_SORTING);

        return $this->bpzs->matching($criteria);
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
        $criteria = Criteria::create()->orderBy(self::ZBAS_SORTING);

        return $this->zbas->matching($criteria);
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

    public function getOriginal(): ?self
    {
        return $this->original;
    }

    public function setOriginal(?self $original): self
    {
        if ($original) {
            if ($original->getId() == $this->getId()) {
                throw new KinderVirtualException('Le kinder ne peut pas être virtuel de lui-même.');
            }
            if ($original->isVirtual()) {
                throw new KinderVirtualException("Le kinder ne peut pas être virtuel d'un autre virtuel.");
            }
            if ($this->hasVirtuals()) {
                throw new KinderVirtualException('Le kinder ne peut pas être virtuel car il a déjà des virtuels de lui-même.');
            }
        }
        $this->original = $original;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getVirtuals(): Collection
    {
        return $this->virtuals;
    }

    public function addVirtual(self $virtual): self
    {
        if (!$this->virtuals->contains($virtual)) {
            $this->virtuals[] = $virtual;
            $virtual->setOriginal($this);
        }

        return $this;
    }

    public function removeVirtual(self $virtual): self
    {
        if ($this->virtuals->contains($virtual)) {
            $this->virtuals->removeElement($virtual);
            // set the owning side to null (unless already changed)
            if ($virtual->getOriginal() === $this) {
                $virtual->setOriginal(null);
            }
        }

        return $this;
    }

    public function isVirtual(): bool
    {
        return $this->original ? true : false;
    }

    public function getAttributes(array $types = []): Collection
    {
        return $this->original ? $this->original->getAttributes($types) : parent::getAttributes($types);
    }

    public function getImage(int $num = 0): ?Image
    {
        return $this->original ? $this->original->getImage($num) : parent::getImage($num);
    }

    public function getImages(): Collection
    {
        return $this->original ? $this->original->getImages() : parent::getImages();
    }

    public function getComment(): string
    {
        return parent::getComment() ?: ($this->original ? $this->original->getComment() : '');
    }

    public function getDescription(): string
    {
        return parent::getDescription() ?: ($this->original ? $this->original->getDescription() : '');
    }

    public function getVariante(): string
    {
        return parent::getVariante() ?: ($this->original ? $this->original->getVariante() : '');
    }

    private function hasVirtuals()
    {
        return !$this->virtuals->isEmpty();
    }
}
