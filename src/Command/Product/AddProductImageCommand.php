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
 * Class AddProductImageCommand
 *
 * Command for add product image
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:image:add', description: 'Add product image')]
class AddProductImageCommand extends Command
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
            ->addOption('image', null, InputOption::VALUE_REQUIRED, 'Image file path')
            ->setHelp(<<<'HELP'
                The <info>%command.name%</info> command allows you to add product image:

                Examples:
                - Add product image:
                    <info>php bin/console %command.name% --product="1" --image="path/to/image.jpg"</info>

                Options:
                --product  (required) Product id
                --image    (required) Image file path
            HELP);
    }

    /**
     * Execute product image add command
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
        $imagePath = $input->getOption('image');

        // check if product id is provided
        if (!$productId) {
            $io->error('The --product option is required.');
            return Command::INVALID;
        }

        // check if image path is provided
        if (!$imagePath) {
            $io->error('The --image option is required.');
            return Command::INVALID;
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $io->error('Product id: ' . $productId . ' not found.');
            return Command::INVALID;
        }

        // add product image
        try {
            $this->productAssetsManager->createProductImage($imagePath, $product);
            $io->success('Product image added: ' . $imagePath . '.');
        } catch (Exception $e) {
            $io->error('Error to add product image: ' . $e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
