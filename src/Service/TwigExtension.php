<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Serie;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    /**
     * @var AttributeChoices
     */
    private $attributeChoices;

    public function __construct(AttributeChoices $attributeChoices)
    {
        $this->attributeChoices = $attributeChoices;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('svgbar', [$this, 'svgbar'], ['is_safe' => ['html']]),
            new TwigFilter('tn', [$this, 'thumbnail']),
            new TwigFilter('imagetype', [$this, 'imagetype']),
            new TwigFilter('attributechunks', [$this, 'attributechunks']),
            new TwigFilter('kinderRefs', [$this, 'kinderReferences']),
        ];
    }

    public function kinderReferences(Serie $serie): array
    {
        $refs = [];
        foreach ($serie->getKinders() as $kinder) {
            if ($kinder->getReference() && !isset($refs[$kinder->getReference()])) {
                $refs[$kinder->getReference()] = $kinder->getName();
            }
        }

        return $refs;
    }

    public function svgbar(array $values, $bgcolor = '#007bff', int $height = 24, int $width = 0)
    {
        if (empty($values)) {
            return '';
        }
        if ($width) {
            $barWidth = $width / \count($values);
        } else {
            $barWidth = 4;
            $width = $barWidth * \count($values);
        }
        $svg = '<svg class="svgbar" height="' . $height . '" width="' . $width . '">';
        $max = max($values) ?: 1;
        foreach (array_values($values) as $x => $value) {
            if ($y = round($height * $value / $max, 4)) {
                $svg .= '<rect data-value="' . $value . '"'
                    . ' fill="' . $bgcolor . '"'
                    . ' x="' . ($x * $barWidth) . '"'
                    . ' y="' . ($height - $y) . '"'
                    . ' width="' . (.9 * $barWidth) . '"'
                    . ' height="' . $y . '"'
                    . '></rect>';
            }
        }
        $svg .= '</svg>';

        return $svg;
    }

    public function attributechunks(FormView $formView)
    {
        return $this->attributeChoices->getChunks($formView);
    }

    public function thumbnail($filename, int $w = 0, int $h = 0)
    {
        $infos = pathinfo($filename);
        if ($w && $h) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.${w}x${h}." . $infos['extension'];
        } elseif ($w) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.${w}." . $infos['extension'];
        } elseif ($h) {
            return $infos['dirname'] . '/' . $infos['filename'] . "_tn.x${h}." . $infos['extension'];
        } else {
            return $infos['dirname'] . '/' . $infos['filename'] . '_tn.' . $infos['extension'];
        }
    }

    public function imagetype($object)
    {
        if (\is_object($object)) {
            $reflection = new \ReflectionClass($object);

            return $reflection->getShortName();
        }

        return '';
    }
}
