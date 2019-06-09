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
use App\Entity\Item;
use App\Entity\Kinder;
use App\Entity\Piece;
use App\Entity\ZBA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VarianteUpdateCommand extends Command
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
            ->setName('admin:variante:migration')
            ->setDescription('Migration des variantes avec le champ adéquat');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classes = [
            BPZ::class,
            Item::class,
            Kinder::class,
            Piece::class,
            ZBA::class,
        ];

        foreach ($classes as $class) {
            foreach ($this->entityManager->getRepository($class)->findAll() as $obj) {
                $comment = $obj->getComment();
                if ($comment && preg_match('!^\s*variante\s*:\s*(.+)$!si', $comment, $matches)) {
                    $obj->setvariante(trim($matches[1]));
                    $obj->setComment('');
                    $this->entityManager->persist($obj);
                }
            }
            $this->entityManager->flush();
        }
    }
}
