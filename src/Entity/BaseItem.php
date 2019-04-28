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

use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseItem extends BaseEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $quantityOwned = 0;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $quantityDouble = 0;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $reference = '';

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $lookingFor = false;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $year = 0;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Image")
     */
    protected $images;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Attribute")
     */
    protected $attributes;

    public function __construct()
    {
        parent::__construct();
        $this->images     = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    public function getQuantityOwned(): int
    {
        return $this->quantityOwned;
    }

    public function setQuantityOwned(int $quantityOwned): self
    {
        $this->quantityOwned = $quantityOwned;
        return $this;
    }

    public function getQuantityDouble(): int
    {
        return $this->quantityDouble;
    }

    public function setQuantityDouble(int $quantityDouble): self
    {
        $this->quantityDouble = $quantityDouble;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function isLookingFor(): bool
    {
        return $this->lookingFor;
    }

    public function setLookingFor(bool $lookingFor): self
    {
        $this->lookingFor = $lookingFor;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        $criteria = Criteria::create()->orderBy(['name' => 'ASC']);
        return $this->images->matching($criteria);
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            if (!$image->getType()) {
                $image->setType(ImageRepository::getTypeFrom($this));
            }
            $this->images[] = $image;
            $this->updateTimestamps();
        }
        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            $this->updateTimestamps();
        }
        return $this;
    }

    public function getImage(int $num = 0): ?Image
    {
        $i = 0;
        foreach ($this->getImages() as $image) {
            if ($i === $num) {
                return $image;
            }
            $i++;
        }
        return null;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(): Collection
    {
        $criteria = Criteria::create()->orderBy(['type' => 'ASC', 'name' => 'ASC']);
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
}
