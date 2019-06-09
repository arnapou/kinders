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
 * @ORM\Entity(repositoryClass="App\Repository\MenuCategoryRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="slug", columns={"slug"}),
 *     @ORM\Index(name="name", columns={"name"})
 * })
 */
class MenuCategory extends BaseEntity
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuItem", mappedBy="category", orphanRemoval=true)
     */
    private $items;

    /**
     * @var string
     * @ORM\Column(type="string", length=40)
     */
    protected $sorting = '';

    public function __construct()
    {
        parent::__construct();
        $this->items = new ArrayCollection();
    }

    /**
     * @return Collection|MenuItem[]
     */
    public function getItems(): Collection
    {
        $criteria = Criteria::create()->orderBy(['sorting' => 'ASC', 'year' => 'DESC', 'name' => 'ASC']);
        return $this->items->matching($criteria);
    }

    public function addItem(MenuItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCategory($this);
        }

        return $this;
    }

    public function removeItem(MenuItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getCategory() === $this) {
                $item->setCategory(null);
            }
        }

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
}
