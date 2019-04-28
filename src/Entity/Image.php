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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_at", columns={"created_at"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="name", columns={"name"}),
 *     @ORM\Index(name="type", columns={"type"}),
 *     @ORM\Index(name="file", columns={"file"}),
 * })
 * @Vich\Uploadable
 */
class Image extends BaseEntity
{
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="vich_images", fileNameProperty="file", size="size")
     *
     * @var File
     */
    private $diskFile;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $type = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $file;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $size;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $linked = false;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type ?: $this->type;
        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function isLinked(): bool
    {
        return $this->linked;
    }

    public function setLinked(bool $linked): void
    {
        $this->linked = $linked;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $diskFile
     */
    public function setDiskFile(?File $diskFile = null)
    {
        $this->diskFile = $diskFile;

        if (null !== $diskFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getDiskFile(): ?File
    {
        return $this->diskFile;
    }
}
