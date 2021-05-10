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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuItemRepository")
 */
class MenuItem extends BaseEntity
{
    public const ATTRIBUTES_SORTING = ['type' => 'ASC', 'name' => 'ASC'];
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MenuCategory", inversedBy="items", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Attribute")
     */
    protected $attributes;

    /**
     * @var string
     * @ORM\Column(type="string", length=40)
     */
    protected $sorting = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $minYear = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxYear = 0;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $routeName = '';

    public function __construct()
    {
        parent::__construct();
        $this->attributes = new ArrayCollection();
    }

    public function getCategory(): ?MenuCategory
    {
        return $this->category;
    }

    public function setCategory(?MenuCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(): Collection
    {
        $criteria = Criteria::create()->orderBy(self::ATTRIBUTES_SORTING);

        return $this->attributes->matching($criteria);
    }

    public function addAttribute(Attribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
        }

        return $this;
    }

    public function removeAttribute(Attribute $attribute): self
    {
        if ($this->attributes->contains($attribute)) {
            $this->attributes->removeElement($attribute);
        }

        return $this;
    }

    public function getMinYear(): ?int
    {
        return $this->routeName ? 0 : $this->minYear;
    }

    public function setMinYear(int $minYear): self
    {
        $this->minYear = $minYear;

        return $this;
    }

    public function getMaxYear(): ?int
    {
        return $this->routeName ? 0 : ($this->maxYear ?: $this->minYear);
    }

    public function setMaxYear(int $maxYear): self
    {
        $this->maxYear = $maxYear;

        return $this;
    }

    public function getSorting(): string
    {
        return $this->sorting;
    }

    public function setSorting(string $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName): self
    {
        $this->routeName = $routeName;

        return $this;
    }
}
