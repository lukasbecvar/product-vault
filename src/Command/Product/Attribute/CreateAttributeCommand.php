<?php

namespace App\Command\Product\Attribute;

use Exception;
use App\Manager\AttributeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateAttributeCommand
 *
 * Command for creating product attribute
 *
 * @package App\Command\Product\Attribute
 */
#[AsCommand(name: 'app:product:attribute:create', description: 'Create product attribute')]
class CreateAttributeCommand extends Command
{
    private AttributeManager $attributeManager;

    public function __construct(AttributeManager $attributeManager)
    {
        $this->attributeManager = $attributeManager;
        parent::__construct();
    }

    /**
     * Configure command arguments and options
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Attribute name');
    }

    /**
     * Execute attribute create command
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // set server headers for cli console
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'CLI-COMMAND';

        // get attribute name
        $name = $input->getArgument('name');

        // check if attribute name is set
        if ($name == null) {
            $io->error('Attribute name is required.');
            return Command::INVALID;
        }

        // create attribute
        try {
            $this->attributeManager->createAttribute($name);
            $io->success('Attribute created successfully.');
        } catch (Exception $e) {
            $io->error('Error to create attribute: ' . $e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
