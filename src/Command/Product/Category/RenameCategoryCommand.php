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
 * Class RenameCategoryCommand
 *
 * Command for renaming product category
 *
 * @package App\Command\Product\Category
 */
#[AsCommand(name: 'app:product:category:rename', description: 'Rename product category')]
class RenameCategoryCommand extends Command
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
        $this->addArgument('old-name', InputArgument::REQUIRED, 'Old category name')
            ->addArgument('new-name', InputArgument::REQUIRED, 'New category name');
    }

    /**
     * Execute category rename command
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

        // get category name to rename
        $oldName = $input->getArgument('old-name');

        // validate category name input
        if ($oldName == null) {
            $io->error('Category name cannot be empty.');
            return Command::INVALID;
        }

        // get new category name
        $newName = $input->getArgument('new-name');

        // validate new category name input
        if ($newName == null) {
            $io->error('New category name cannot be empty.');
            return Command::INVALID;
        }

        // get category by old name
        $category = $this->categoryManager->getCategoryByName($oldName);

        // check if category found
        if ($category === null) {
            $io->error('Category not found: ' . $oldName . '.');
            return Command::INVALID;
        }

        // get category id
        $id = $category->getId();

        // check if category id is set
        if ($id == null) {
            $io->error('Error to get category id.');
            return Command::INVALID;
        }

        // rename category
        try {
            $this->categoryManager->renameCategory($id, $newName);
            $io->success('Category renamed successfully.');
        } catch (Exception $e) {
            $io->error('Error to rename category: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
