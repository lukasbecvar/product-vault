<?php

namespace App\Command\UserManager;

use Exception;
use App\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UserRegisterCommand
 *
 * Command to register new user
 *
 * @package App\Command\UserManager
 */
#[AsCommand(name: 'app:user:register', description: 'Register user')]
class UserRegisterCommand extends Command
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    /**
     * Execute user registration command
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // fix visitor ip address in cli mode
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // get email from cli input
        $email = $io->ask('Enter user email');
        if ($email == null) {
            $io->error('Email cannot be empty.');
            return Command::INVALID;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email format.');
            return Command::INVALID;
        }

        // get first name from cli input
        $firstName = $io->ask('Enter first name');
        if ($firstName == null) {
            $io->error('First name cannot be empty.');
            return Command::INVALID;
        }

        // get last name from cli input
        $lastName = $io->ask('Enter last name');
        if ($lastName == null) {
            $io->error('Last name cannot be empty.');
            return Command::INVALID;
        }

        // get password from cli input
        $password = $io->askHidden('Enter password (hidden input)');
        if ($password == null) {
            $io->error('Password cannot be empty.');
            return Command::INVALID;
        }

        // check if user already exists
        if ($this->userManager->isUserExists($email)) {
            $io->error('User already exists: ' . $email);
            return Command::INVALID;
        }

        // register user
        try {
            $this->userManager->registerUser($email, $firstName, $lastName, $password);
            $io->success("User registered: $email ($firstName $lastName)");
        } catch (Exception $e) {
            $io->error('Error registering user: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
