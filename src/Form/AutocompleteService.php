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

use App\Entity\BaseEntity;
use App\Entity\BaseItem;
use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Service\Admin\SearchFilter;
use App\Service\ImageHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteService
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $doctrine,
        private ImageHelper $imageHelper,
        private SearchFilter $searchFilter
    ) {
    }

    public function images(Request $request, $type)
    {
        $form = $this->formFactory->create($type);
        $fieldOptions = $form->get($request->get('field_name'))->getConfig()->getOptions();
        $dataClass = $form->get($request->get('field_name'))->getParent()->getConfig()->getDataClass();

        $imageType = ImageRepository::getTypeFrom($dataClass);

        /** @var ImageRepository $repo */
        $repo = $this->doctrine->getRepository($fieldOptions['class']);

        $term = str_replace('*', '%', (string) $request->get('q'));

        $qbCount = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbCount
            ->select($qbCount->expr()->count('e'))
            ->andWhere('e.type = :type')->setParameter('type', $imageType);
        if ('' === $term) {
            $qbCount->andWhere('e.linked = :linked')->setParameter('linked', false);
        }

        $maxResults = $fieldOptions['page_limit'] ?? 20;
        $offset = ($request->get('page', 1) - 1) * $maxResults;

        $qbResult = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbResult
            ->andWhere('e.type = :type')->setParameter('type', $imageType)
            ->setMaxResults($maxResults)->setFirstResult($offset);
        if ('' === $term) {
            $qbResult->andWhere('e.linked = :linked')->setParameter('linked', false);
        }

        $count = $qbCount->getQuery()->getSingleScalarResult();
        $paginationResults = $qbResult->getQuery()->getResult();

        return [
            'results' => $this->mapImageToArray($paginationResults),
            'more' => $count > ($offset + $maxResults),
        ];
    }

    private function mapImageToArray($paginationResults): array
    {
        return array_map(
            fn (Image $image) => $image->getFile()
                ? [
                    'id' => $image->getId(),
                    'text' => (string) $image,
                    'file' => $this->imageHelper->asset($image),
                ]
                : [
                    'id' => $image->getId(),
                    'text' => (string) $image,
                ],
            $paginationResults
        );
    }

    public function entities(Request $request, $type, $class = null)
    {
        $form = $this->formFactory->create($type);
        $fieldOptions = $form->get($request->get('field_name'))->getConfig()->getOptions();

        /** @var ImageRepository $repo */
        $repo = $this->doctrine->getRepository($class ?: $fieldOptions['class']);

        $term = str_replace('*', '%', (string) $request->get('q'));

        $qbCount = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbCount->select($qbCount->expr()->count('e'));

        $maxResults = $fieldOptions['page_limit'] ?? 20;
        $offset = ($request->get('page', 1) - 1) * $maxResults;

        $qbResult = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbResult->setMaxResults($maxResults)->setFirstResult($offset);

        $count = $qbCount->getQuery()->getSingleScalarResult();
        $paginationResults = $qbResult->getQuery()->getResult();

        return [
            'results' => $this->mapToArray($paginationResults),
            'more' => $count > ($offset + $maxResults),
        ];
    }

    private function mapToArray($paginationResults): array
    {
        return array_map(
            fn (BaseEntity $entity) => ($entity instanceof BaseItem && $entity->getImage())
                ? [
                    'id' => $entity->getId(),
                    'text' => (string) $entity,
                    'file' => $this->imageHelper->asset($entity->getImage()),
                ]
                : [
                    'id' => $entity->getId(),
                    'text' => (string) $entity,
                ],
            $paginationResults
        );
    }
}
