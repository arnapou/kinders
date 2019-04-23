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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminUserDeleteCommand extends AdminUserCreateCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:delete')
            ->setDescription('Supprimer un admin')
            ->addArgument('admin', InputArgument::REQUIRED, 'ID or USERNAME');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('admin');
        if (ctype_digit($username)) {
            $admin = $this->repository->find($username);
        } else {
            $admin = $this->repository->findOneBy(['username' => $username]);
        }
        if ($admin) {
            $this->entityManager->remove($admin);
            $this->entityManager->flush();
            $output->writeln('Utilisateur supprimé !');
        } else {
            $output->writeln('<error>Utilisateur introuvable !</error>');
        }

        $output->writeln('');
        foreach ($this->repository->findAll() as $adminUser) {
            $output->writeln('<info>#' . $adminUser->getId() . '</info> ' . $adminUser->getUsername());
        }
    }
}
