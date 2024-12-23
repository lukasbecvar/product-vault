<?php

namespace App\Command\Product\Category;

use Exception;
use App\Manager\CategoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteCategoryCommand
 *
 * Command for deleting product category
 *
 * @package App\Command\Product\Category
 */
#[AsCommand(name: 'app:product:category:delete', description: 'Delete product category')]
class DeleteCategoryCommand extends Command
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
     * Execute category delete command
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

        // validate category name input
        if ($name == null) {
            $io->error('Category name is required.');
            return Command::INVALID;
        }

        // check if category name exists
        if (!$this->categoryManager->checkIfCategoryNameAlreadyExists($name)) {
            $io->error('Category not found: ' . $name);
            return Command::INVALID;
        }

        // get category by name
        $category = $this->categoryManager->getCategoryByName($name);

        // check if category found
        if ($category === null) {
            $io->error('Category not found: ' . $name);
            return Command::INVALID;
        }

        // get category id
        $id = $category->getId();

        // check if category id is set
        if ($id == null) {
            $io->error('Error to get category id.');
            return Command::INVALID;
        }

        // delete category
        try {
            $this->categoryManager->deleteCategory($id);
            $io->success('Category deleted successfully');
        } catch (Exception $e) {
            $io->error('Error to delete category: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
