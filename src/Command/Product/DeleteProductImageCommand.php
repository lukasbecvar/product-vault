<?php

namespace App\Command\Product;

use Exception;
use App\Manager\ProductManager;
use App\Manager\ProductAssetsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteProductImageCommand
 *
 * Command for deleting product image
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:image:delete', description: 'Delete product image')]
class DeleteProductImageCommand extends Command
{
    private ProductManager $productManager;
    private ProductAssetsManager $productAssetsManager;

    public function __construct(ProductManager $productManager, ProductAssetsManager $productAssetsManager)
    {
        $this->productManager = $productManager;
        $this->productAssetsManager = $productAssetsManager;
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
            ->addOption('image', null, InputOption::VALUE_REQUIRED, 'Image id')
            ->setHelp(<<<'HELP'
                The <info>%command.name%</info> command allows you to delete product image:

                Examples:
                - Delete product image:
                    <info>php bin/console %command.name% --product="1" --image="path/to/image.jpg"</info>

                Options:
                --product  (required) Product id
                --image    (required) Image id
            HELP);
    }

    /**
     * Execute product image delete command
     *
     * @param InputInterface $input The command input
     * @param OutputInterface $output The command output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // fix get visitor info for cli mode
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'CLI-COMMAND';

        // get command options
        $productId = $input->getOption('product');
        $imageId = $input->getOption('image');

        // check if product id is provided
        if (!$productId) {
            $io->error('The --product option is required.');
            return Command::INVALID;
        }

        // check if image id is provided
        if (!$imageId) {
            $io->error('The --image option is required.');
            return Command::INVALID;
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $io->error('Product not found: ' . $productId . '.');
            return Command::INVALID;
        }

        // check if product have image
        if (!$this->productAssetsManager->checkIfProductHaveImage($product, $imageId)) {
            $io->error('Product: ' . $productId . ' does not have image: ' . $imageId . '.');
            return Command::INVALID;
        }

        // delete product image
        try {
            $this->productAssetsManager->deleteProductImage($imageId);
            $io->success('Product image deleted: ' . $imageId . '.');
        } catch (Exception $e) {
            $io->error('Error to delete product image: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
