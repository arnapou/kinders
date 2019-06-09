<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\BPZ;
use App\Entity\Collection;
use App\Entity\Country;
use App\Entity\Image;
use App\Entity\Item;
use App\Entity\Kinder;
use App\Entity\Piece;
use App\Entity\Serie;
use App\Entity\ZBA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlugUpdateCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('admin:slug:update')
            ->setDescription('Met à jour tous les slugs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classes = [
            BPZ::class,
            Collection::class,
            Country::class,
            Image::class,
            Item::class,
            Kinder::class,
            Piece::class,
            Serie::class,
            ZBA::class,
        ];

        foreach ($classes as $class) {
            foreach ($this->entityManager->getRepository($class)->findAll() as $obj) {
                $obj->updateSlug();
            }
            $this->entityManager->flush();
        }
    }
}
