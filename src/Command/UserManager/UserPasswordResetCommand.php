<?php

namespace App\Command\UserManager;

use Exception;
use App\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UserPasswordResetCommand
 *
 * Command to reset user password
 *
 * @package App\Command\UserManager
 */
#[AsCommand(name: 'app:password:reset', description: 'Reset user password')]
class UserPasswordResetCommand extends Command
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    /**
     * Configure command arguments
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the user');
    }

    /**
     * Execute user password reset command
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // fix get visitor info for cli mode
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'CLI-COMMAND';

        // get email argument
        $email = $input->getArgument('email');

        if ($email == null) {
            $io->error('Email cannot be empty.');
            return Command::INVALID;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email format.');
            return Command::INVALID;
        }

        // check if user exists
        if (!$this->userManager->checkIfUserEmailAlreadyRegistered($email)) {
            $io->error('User not found: ' . $email);
            return Command::INVALID;
        }

        // get user id by email
        $id = $this->userManager->getUserIdByEmail($email);

        try {
            // reset and get new password
            $newPassword = $this->userManager->resetUserPassword($id);
            $io->success('User password reset: ' . $email . ' the new password is: ' . $newPassword);
        } catch (Exception $e) {
            $io->error('Error resetting user password: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
