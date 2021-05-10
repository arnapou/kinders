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

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminUserCreateCommand extends Command
{
    /**
     * @var AdminUserRepository
     */
    protected $repository;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    public function __construct(
        AdminUserRepository $repository,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder
    ) {
        parent::__construct();
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    protected function configure()
    {
        $this
            ->setName('admin:user:create')
            ->setDescription('Créer ou modifier un admin')
            ->addArgument('username', InputArgument::REQUIRED, 'username');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $question = new Question('Password: ');
        $question->setHidden(true);
        $password = $io->askQuestion($question);
        $username = $input->getArgument('username');

        $admin = $this->repository->findOneBy(['username' => $username]) ?: new AdminUser();
        $admin->setUsername($username);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->encoder->encodePassword($admin, $password));
        $created = $admin->getId();

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $output->writeln('Utilisateur ' . ($created ? 'modifié' : 'créé') . ' !');

        $output->writeln('');
        foreach ($this->repository->findAll() as $adminUser) {
            $output->writeln('<info>#' . $adminUser->getId() . '</info> ' . $adminUser->getUsername());
        }
    }
}
