<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Image;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

class VichNamers implements NamerInterface, DirectoryNamerInterface
{
    use FileExtensionTrait;

    private const LENGTH = 8;
    private const CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Creates a directory name for the file being uploaded.
     *
     * @param Image           $object  The object the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The directory name
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        return $object->getType() . '/' . ($object->getFile()[0] ?? 'a');
    }

    /**
     * Creates a name for the file being uploaded.
     *
     * @param object          $object  The object the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The file name
     */
    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $name = '';
        for ($i = 0; $i < self::LENGTH; ++$i) {
            $name .= self::CHARS[random_int(0, \strlen(self::CHARS) - 1)];
        }

        if ($extension = $this->getExtension($file)) {
            $name = sprintf('%s.%s', $name, strtolower($extension));
        }

        return $name;
    }
}
