<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Admin;

use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AdminTwigExtension extends AbstractExtension
{
    public function __construct(private AttributeChoices $attributeChoices)
    {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('svgbar', [$this, 'svgbar'], ['is_safe' => ['html']]),
            new TwigFilter('imagetype', [$this, 'imagetype']),
            new TwigFilter('attributechunks', [$this, 'attributechunks']),
        ];
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
        $svg = '';
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

        return '<svg class="svgbar" height="' . $height . '" width="' . $width . '">' . $svg . '</svg>';
    }

    public function attributechunks(FormView $formView)
    {
        return $this->attributeChoices->getChunks($formView);
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
