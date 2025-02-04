<?php

namespace App\Command\Product;

use Exception;
use App\Manager\ProductManager;
use App\Manager\CategoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateProductCategoryCommand
 *
 * Command for updating product category
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:category:update', description: 'Update product category')]
class UpdateProductCategoryCommand extends Command
{
    private ProductManager $productManager;
    private CategoryManager $categoryManager;

    public function __construct(ProductManager $productManager, CategoryManager $categoryManager)
    {
        $this->productManager = $productManager;
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
        $this
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product id')
            ->addOption('add', null, InputOption::VALUE_REQUIRED, 'Category id to add')
            ->addOption('remove', null, InputOption::VALUE_REQUIRED, 'Category id to remove')
            ->setHelp(<<<'HELP'
                The <info>%command.name%</info> command allows you to add or remove categories to a product:

                Examples:
                - Add a category:
                    <info>php bin/console %command.name% --product="1" --add="2"</info>
                
                - Remove a category:
                    <info>php bin/console %command.name% --product="1" --remove="2"</info>

                - If no category action (--add or --remove) is provided, the command will display an error.

                Options:
                --product  (required) Product id
                --add      (optional) Category id to add
                --remove   (optional) Category id to remove
            HELP)
        ;
    }

    /**
     * Execute product category update command
     *
     * @param InputInterface $input The command input
     * @param OutputInterface $output The command output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // set server headers for cli console
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'CLI-COMMAND';

        // get command options
        $productId = $input->getOption('product');
        $categoryIdToAdd = $input->getOption('add');
        $categoryIdToRemove = $input->getOption('remove');

        // check if product id is provided
        if (!$productId) {
            $io->error('The --product option is required.');
            return Command::INVALID;
        }

        // check if category id to add or remove is provided
        if (!$categoryIdToAdd && !$categoryIdToRemove) {
            $io->warning('No category action provided. Use --add or --remove.');
            return Command::INVALID;
        }

        if ($categoryIdToAdd != null) {
            // get category by id
            $category = $this->categoryManager->getCategoryById($categoryIdToAdd);
            if ($category == null) {
                $io->error('Category: ' . $categoryIdToAdd . ' not found.');
                return Command::INVALID;
            }

            // get product by id
            $product = $this->productManager->getProductById($productId);
            if ($product == null) {
                $io->error('Product id: ' . $productId . ' not found.');
                return Command::INVALID;
            }

            try {
                $this->productManager->assignCategoryToProduct($product, $category);
                $io->success('Category: ' . $category->getName() . ' added to product: ' . $product->getName() . '.');
            } catch (Exception $e) {
                $io->error('Error adding category to product: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        if ($categoryIdToRemove != null) {
            // get category by id
            $category = $this->categoryManager->getCategoryById($categoryIdToRemove);
            if ($category == null) {
                $io->error('Category: ' . $categoryIdToRemove . ' not found.');
                return Command::INVALID;
            }

            // get product by id
            $product = $this->productManager->getProductById($productId);
            if ($product == null) {
                $io->error('Product id: ' . $productId . ' not found.');
                return Command::INVALID;
            }

            try {
                $this->productManager->removeCategoryFromProduct($product, $category);
                $io->success('Category: ' . $category->getName() . ' removed from product: ' . $product->getName() . '.');
            } catch (Exception $e) {
                $io->error('Error removing category from product: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }
}
