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

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    const ATTRIBUTES_MAX_COLUMNS = 10;
    const ATTRIBUTES_MIN_COLUMN_COUNT = 5;

    public function getFilters()
    {
        return [
            new TwigFilter('tn', [$this, 'thumbnail']),
            new TwigFilter('imagetype', [$this, 'imagetype']),
            new TwigFilter('attributechunks', [$this, 'attributechunks']),
        ];
    }

    public function attributechunks(FormView $view)
    {
        $choiceTypes = $view->vars['choices'];
        ksort($choiceTypes);

        $findChild = function (ChoiceView $choice) use ($view) {
            foreach ($view as $child) {
                if ($child->vars['value'] == $choice->value) {
                    return $child;
                }
            }
            return null;
        };

        $items = [];
        foreach ($choiceTypes as $type => $choices) {
            $items[] = ['title' => $type];
            foreach ($choices as $choice) {
                $items[] = ['choice' => $findChild($choice)];
            }
        }

        if (\count($items) <= self::ATTRIBUTES_MIN_COLUMN_COUNT) {
            return [$items];
        }

        $nbColumns = ceil(\count($items) / self::ATTRIBUTES_MIN_COLUMN_COUNT);
        $nbColumns = $nbColumns <= self::ATTRIBUTES_MAX_COLUMNS ? $nbColumns : self::ATTRIBUTES_MAX_COLUMNS;
        $chunkSize = ceil(\count($items) / $nbColumns);

        $chunks = [];
        $chunk  = [];
        foreach ($items as $item) {
            if (\count($chunk) == $chunkSize ||
                \count($chunk) == $chunkSize - 1 && isset($item['title'])
            ) {
                $chunks[] = $chunk;
                $chunk    = [];
            }
            $chunk[] = $item;
        }
        if ($chunk) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    public function thumbnail($filename)
    {
        $infos = pathinfo($filename);
        return $infos['dirname'] . '/' . $infos['filename'] . '_tn.' . $infos['extension'];
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
