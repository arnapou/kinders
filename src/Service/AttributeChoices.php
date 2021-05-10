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

use Symfony\Component\Form\FormView;

class AttributeChoices
{
    public const MAX_COLUMNS = 10;
    public const MIN_COLUMN_COUNT = 5;

    private array $numericTypes = ['poids'];

    public function getChunks(FormView $formView): array
    {
        $choiceTypes = $this->getSortedChoicesTypes($formView);

        $items = [];
        foreach ($choiceTypes as $type => $choices) {
            $items[] = ['title' => $type];
            foreach ($choices as $id => $choice) {
                $items[] = ['choice' => $this->findChild($formView, $id)];
            }
        }

        return $this->chunk($items);
    }

    private function getSortedChoicesTypes(FormView $formView): array
    {
        $choiceTypes = [];
        foreach ($formView->vars['choices'] as $choices) {
            foreach ($choices as $choice) {
                $choiceTypes[$choices->label][$choice->value] = $choice->label;
            }
            if (\in_array($choices->label, $this->numericTypes)) {
                asort($choiceTypes[$choices->label], SORT_NUMERIC);
            } else {
                asort($choiceTypes[$choices->label], SORT_STRING | SORT_FLAG_CASE);
            }
        }
        ksort($choiceTypes, SORT_STRING | SORT_FLAG_CASE);

        return $choiceTypes;
    }

    private function findChild(FormView $formView, int $id)
    {
        foreach ($formView as $child) {
            if ((string) $child->vars['value'] === (string) $id) {
                return $child;
            }
        }

        return null;
    }

    private function chunk(array $items): array
    {
        if (\count($items) <= self::MIN_COLUMN_COUNT) {
            return [$items];
        }
        $nbColumns = (int) ceil(\count($items) / self::MIN_COLUMN_COUNT);
        $nbColumns = $nbColumns <= self::MAX_COLUMNS ? $nbColumns : self::MAX_COLUMNS;
        $chunkSize = (int) ceil(\count($items) / $nbColumns);
        $chunks = [];
        $chunk = [];
        $chunked = 0;
        foreach ($items as $item) {
            if (\count($chunks) < $nbColumns &&
                (
                    // max size chunk atteint
                    \count($chunk) === $chunkSize ||
                    // un title est le dernier element du chunk => on le passe a la colonne suivante
                    \count($chunk) === $chunkSize - 1 && isset($item['title']) ||
                    // un title dans l'avant derniere colonne avec suffisamment de place pour passer le reste dans la derniere colonne
                    \count($chunks) === $nbColumns - 2 && isset($item['title']) && \count($items) - $chunked <= $chunkSize
                )
            ) {
                $chunks[] = $chunk;
                $chunk = [];
            }
            $chunk[] = $item;
            ++$chunked;
        }
        if ($chunk) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }
}
