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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminUserListCommand extends AdminUserCreateCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:list')
            ->setDescription('Liste les admins');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->repository->findAll() as $adminUser) {
            $output->writeln('<info>#' . $adminUser->getId() . '</info> ' . $adminUser->getUsername());
        }
    }
}
