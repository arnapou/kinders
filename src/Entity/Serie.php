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
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SerieRepository")
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
class Serie extends BaseItem
{
    public const KINDER_SORTING = ['realsorting' => 'ASC', 'name' => 'ASC'];
    public const PIECES_SORTING = ['realsorting' => 'ASC', 'name' => 'ASC'];
    public const ITEMS_SORTING = ['realsorting' => 'ASC', 'name' => 'ASC'];

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Kinder", mappedBy="serie", orphanRemoval=true)
     */
    private $kinders;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Piece", mappedBy="serie", orphanRemoval=true)
     */
    private $pieces;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="serie", orphanRemoval=true)
     */
    private $items;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Collection", inversedBy="series", fetch="EAGER")
     */
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->kinders = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->pieces = new ArrayCollection();
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getMaxBpzCount(): int
    {
        $max = 0;
        foreach ($this->kinders as $kinder) {
            $max = max($max, $kinder->getBpzs()->count());
        }

        return $max;
    }

    public function getMaxZbaCount(): int
    {
        $max = 0;
        foreach ($this->kinders as $kinder) {
            $max = max($max, $kinder->getZbas()->count());
        }

        return $max;
    }

    /**
     * @return DoctrineCollection|Kinder[]
     */
    public function getKinders(): DoctrineCollection
    {
        $criteria = Criteria::create()->orderBy(self::KINDER_SORTING);

        return $this->kinders->matching($criteria);
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

    /**
     * @return DoctrineCollection|Piece[]
     */
    public function getPieces(): DoctrineCollection
    {
        $criteria = Criteria::create()->orderBy(self::PIECES_SORTING);

        return $this->pieces->matching($criteria);
    }

    public function addPiece(Piece $piece): self
    {
        if (!$this->pieces->contains($piece)) {
            $this->pieces[] = $piece;
            $piece->setSerie($this);
        }

        return $this;
    }

    public function removePiece(Piece $piece): self
    {
        if ($this->pieces->contains($piece)) {
            $this->pieces->removeElement($piece);
            // set the owning side to null (unless already changed)
            if ($piece->getSerie() === $this) {
                $piece->setSerie(null);
            }
        }

        return $this;
    }

    /**
     * @return DoctrineCollection|Item[]
     */
    public function getItems(): DoctrineCollection
    {
        $criteria = Criteria::create()->orderBy(self::ITEMS_SORTING);

        return $this->items->matching($criteria);
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setSerie($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getSerie() === $this) {
                $item->setSerie(null);
            }
        }

        return $this;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function isPuzzle(): bool
    {
        return $this->hasAttribute('kinder', 'Puzzle');
    }

    public function getPuzzle(): ?array
    {
        if (!$this->isPuzzle()) {
            return null;
        }
        $kinders = [];
        $w = $h = 0;
        foreach ($this->kinders as $kinder) {
            foreach ($kinder->getAttributes() as $attribute) {
                if ('puzzle' === strtolower($attribute->getType()) && preg_match('!^(\d+):(\d+)$!', $attribute->getName(), $matches)) {
                    $kinders[$matches[1] - 1][$matches[2] - 1] = $kinder;

                    $w = max($w, $matches[2]);
                    $h = max($h, $matches[1]);
                }
            }
        }

        return [
            'width' => $w,
            'height' => $h,
            'kinders' => $kinders,
        ];
    }

    public function getImage(int $num = 0): ?Image
    {
        foreach ($this->getKinders() as $kinder) {
            if ($image = $kinder->getImage()) {
                return $image;
            }
        }

        return null;
        // $image = parent::getImage($num);
        // if (0 === $num && null === $image) {
        //     foreach ($this->getKinders() as $kinder) {
        //         if ($image = $kinder->getImage()) {
        //             break;
        //         }
        //     }
        // }
        //
        // return $image;
    }

    public function isComplete(): bool
    {
        foreach ($this->getKinders() as $kinder) {
            if ($kinder->isLookingFor()) {
                return false;
            }
            foreach ($kinder->getBpzs() as $bpz) {
                if ($bpz->isLookingFor()) {
                    return false;
                }
            }
            foreach ($kinder->getZbas() as $zba) {
                if ($zba->isLookingFor()) {
                    return false;
                }
            }
        }

        return true;
    }
}
