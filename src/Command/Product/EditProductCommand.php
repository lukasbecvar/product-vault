<?php

namespace App\Command\Product;

use Exception;
use App\Manager\ProductManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EditProductCommand
 *
 * Command for editing product
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:edit', description: 'Edit product')]
class EditProductCommand extends Command
{
    private ProductManager $productManager;

    public function __construct(ProductManager $productManager)
    {
        parent::__construct();
        $this->productManager = $productManager;
    }

    /**
     * Configure command arguments and options
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Product id');
    }

    /**
     * Execute product edit command
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

        // get product id from cli input
        $id = $input->getArgument('id');

        // check if product id is set
        if ($id == null) {
            $io->error('Product id cannot be empty.');
            return Command::INVALID;
        }

        // validate product id input
        if (!is_numeric($id)) {
            $io->error('Invalid product id format.');
            return Command::INVALID;
        }

        // cast product id to integer
        $id = (int) $id;

        // get product by id
        $productToEdit = $this->productManager->getProductById($id);

        // check if product id exists
        if ($productToEdit == null) {
            $io->error('Product not found: ' . $id);
            return Command::INVALID;
        }

        // get new data from cli input
        $name = $io->ask('Enter product name (if you want to keep the same, press enter)');
        $description = $io->ask('Enter product description (if you want to keep the same, press enter)');
        $price = $io->ask('Enter product price (if you want to keep the same, press enter)');
        $priceCurrency = $io->ask('Enter product price currency (if you want to keep the same, press enter)');

        // edit product
        try {
            $this->productManager->editProduct($id, $name, $description, $price, $priceCurrency);
            $io->success('Product: ' . $productToEdit->getName() . ' edited.');
        } catch (Exception $e) {
            $io->error('Error to edit product: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
