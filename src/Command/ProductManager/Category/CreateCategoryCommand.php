<?php

namespace App\Command\ProductManager\Category;

use Exception;
use App\Manager\CategoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCategoryCommand
 *
 * Command for creating product category
 *
 * @package App\Command\ProductManager\Category
 */
#[AsCommand(name: 'app:product:category:create', description: 'Create product category')]
class CreateCategoryCommand extends Command
{
    private CategoryManager $categoryManager;

    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
        parent::__construct();
    }

    /**
     * Configure command arguments and options
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Category name');
    }

    /**
     * Execute category create command
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

        // get category name
        $name = $input->getArgument('name');

        // check if category name is set
        if ($name == null) {
            $io->error('Category name is required.');
            return Command::INVALID;
        }

        // create category
        try {
            $this->categoryManager->createCategory($name);
            $io->success('Category created successfully');
        } catch (Exception $e) {
            $io->error('Error to create category: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
