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
     * @ORM\Column(type="string", length=40)
     */
    protected $reference = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=40)
     */
    protected $sorting = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $realsorting = '#';

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

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $variante = '';

    public function __construct()
    {
        parent::__construct();
        $this->images = new ArrayCollection();
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
        $reference = str_replace(',', '.', $reference);
        $this->reference = $reference;
        $this->calcRealsorting();

        return $this;
    }

    public function getSorting(): string
    {
        return $this->sorting;
    }

    public function setSorting(string $sorting): self
    {
        $this->sorting = $sorting;
        $this->calcRealsorting();

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

    public function calcRealsorting(): self
    {
        $this->realsorting = preg_replace_callback(
            '!(\d+)!',
            function ($matches) {
                return sprintf('%04d', $matches[1]);
            },
            $this->sorting . '#' . $this->reference
        );

        return $this;
    }

    public function getRealsorting(): string
    {
        return $this->realsorting;
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
            ++$i;
        }

        return null;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(array $types = []): Collection
    {
        $criteria = Criteria::create()->orderBy(['type' => 'ASC', 'name' => 'ASC']);

        return $this->attributes->matching($criteria)->filter(function (Attribute $attr) use ($types) {
            return $types ? \in_array($attr->getType(), $types) : true;
        });
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

    public function hasAttribute(string $type, ?string $name = null): bool
    {
        $type = strtolower($type);
        $name = strtolower($name);
        foreach ($this->attributes as $attribute) {
            if (strtolower($attribute->getType()) === $type &&
                (!$name || strtolower($attribute->getName()) === $name)
            ) {
                return true;
            }
        }

        return false;
    }

    public function getVariante(): string
    {
        return $this->variante;
    }

    public function setVariante(string $variante): self
    {
        $this->variante = $variante;

        return $this;
    }
}
