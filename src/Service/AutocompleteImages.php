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

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @var Packages
     */
    private $packages;

    public function __construct(FormFactoryInterface $formFactory, ManagerRegistry $doctrine, Packages $packages)
    {
        $this->formFactory = $formFactory;
        $this->doctrine    = $doctrine;
        $this->packages    = $packages;
    }

    public function getResult(Request $request, $type)
    {
        $form         = $this->formFactory->create($type);
        $fieldOptions = $form->get($request->get('field_name'))->getConfig()->getOptions();

        $imageType = ImageTypeGuesser::guess($fieldOptions['source_class']);

        /** @var ImageRepository $repo */
        $repo = $this->doctrine->getRepository($fieldOptions['class']);

        $term = $request->get('q');

        $countQB = $repo->searchQB([$term]);
        $countQB
            ->select($countQB->expr()->count('i'))
            ->andWhere('i.type = :type')->setParameter('type', $imageType);

        $maxResults = $fieldOptions['page_limit'];
        $offset     = ($request->get('page', 1) - 1) * $maxResults;

        $resultQb = $repo->searchQB([$term]);
        $resultQb
            ->andWhere('i.type = :type')->setParameter('type', $imageType)
            ->setMaxResults($maxResults)
            ->setFirstResult($offset);

        $count             = $countQB->getQuery()->getSingleScalarResult();
        $paginationResults = $resultQb->getQuery()->getResult();

        $result = ['results' => null, 'more' => $count > ($offset + $maxResults)];

        $result['results'] = array_map(
            function (Image $item) {
                $array = ['id' => $item->getId(), 'text' => \strval($item)] +
                    ($item->getFile() ? ['file' => $this->packages->getUrl($item->getFile())] : []);
                return $array;
            },
            $paginationResults
        );

        return $result;
    }
}
