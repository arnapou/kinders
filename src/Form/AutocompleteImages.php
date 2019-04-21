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
use App\Repository\ImageRepository;
use App\Service\SearchFilter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AutocompleteImages
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;
    /**
     * @var SearchFilter
     */
    private $searchFilter;

    public function __construct(
        FormFactoryInterface $formFactory,
        ManagerRegistry $doctrine,
        UploaderHelper $uploaderHelper,
        SearchFilter $searchFilter
    ) {
        $this->formFactory    = $formFactory;
        $this->doctrine       = $doctrine;
        $this->uploaderHelper = $uploaderHelper;
        $this->searchFilter   = $searchFilter;
    }

    public function getResult(Request $request, $type)
    {
        $form         = $this->formFactory->create($type);
        $fieldOptions = $form->get($request->get('field_name'))->getConfig()->getOptions();
        $dataClass    = $form->get($request->get('field_name'))->getParent()->getConfig()->getDataClass();

        $imageType = ImageRepository::getTypeFrom($dataClass);

        /** @var ImageRepository $repo */
        $repo = $this->doctrine->getRepository($fieldOptions['class']);

        $term = $request->get('q');

        $qbCount = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbCount
            ->select($qbCount->expr()->count('e'))
            ->andWhere('e.type = :type')->setParameter('type', $imageType);

        $maxResults = $fieldOptions['page_limit'];
        $offset     = ($request->get('page', 1) - 1) * $maxResults;

        $qbResult = $this->searchFilter->searchQueryBuilder($repo, [$term]);
        $qbResult
            ->andWhere('e.type = :type')->setParameter('type', $imageType)
            ->setMaxResults($maxResults)
            ->setFirstResult($offset);

        $count             = $qbCount->getQuery()->getSingleScalarResult();
        $paginationResults = $qbResult->getQuery()->getResult();

        return [
            'results' => $this->mapToArray($paginationResults),
            'more'    => $count > ($offset + $maxResults),
        ];
    }

    private function mapToArray($paginationResults): array
    {
        return array_map(
            function (Image $item) {
                return [
                        'id'   => $item->getId(),
                        'text' => \strval($item),
                    ] +
                    ($item->getFile() ? ['file' => $this->file($item)] : []);
            },
            $paginationResults
        );
    }

    private function file(Image $image)
    {
        return $this->uploaderHelper->asset($image, 'diskFile');
    }
}
