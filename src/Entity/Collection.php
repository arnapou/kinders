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
 * @ORM\Entity(repositoryClass="App\Repository\CollectionRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="slug", columns={"slug"}),
 *     @ORM\Index(name="name", columns={"name"})
 * })
 */
class Collection extends BaseEntity
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Serie", mappedBy="collection")
     */
    private $series;

    public function __construct()
    {
        parent::__construct();
        $this->series = new ArrayCollection();
    }

    /**
     * @return DoctrineCollection|Serie[]
     */
    public function getSeries(): DoctrineCollection
    {
        $criteria = Criteria::create()->orderBy(['country.sorting' => 'ASC', 'country.name' => 'ASC', 'name' => 'ASC', 'year' => 'ASC']);
        $iterator = $this->series->getIterator();
        $iterator->uasort(function (Serie $a, Serie $b) {
            return ($a->getCountry()->getSorting() <=> $b->getCountry()->getSorting())
                ?: ($a->getCountry()->getName() <=> $b->getCountry()->getName());
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function addSeries(Serie $series): self
    {
        if (!$this->series->contains($series)) {
            $this->series[] = $series;
            $series->setCollection($this);
        }

        return $this;
    }

    public function removeSeries(Serie $series): self
    {
        if ($this->series->contains($series)) {
            $this->series->removeElement($series);
            // set the owning side to null (unless already changed)
            if ($series->getCollection() === $this) {
                $series->setCollection(null);
            }
        }

        return $this;
    }
}
