<?php

namespace App\Command\Product;

use Exception;
use App\DTO\ProductDTO;
use App\Manager\ProductManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateProductCommand
 *
 * Command for creating new product
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:create', description: 'Create product')]
class CreateProductCommand extends Command
{
    private ProductManager $productManager;
    private ValidatorInterface $validator;

    public function __construct(ProductManager $productManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->productManager = $productManager;
    }

    /**
     * Execute product create command
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

        // get product name from cli input
        $name = $io->ask('Enter product name:');
        if ($name == null) {
            $io->error('Product name cannot be empty.');
            return Command::INVALID;
        }

        // get product description from cli input
        $description = $io->ask('Enter product description:');
        if ($description == null) {
            $io->error('Product description cannot be empty.');
            return Command::INVALID;
        }

        // get product price from cli input
        $price = $io->ask('Enter product price:');
        if ($price == null) {
            $io->error('Product price cannot be empty.');
            return Command::INVALID;
        }
        if (!is_numeric($price)) {
            $io->error('Invalid product price format.');
            return Command::INVALID;
        }

        // get product price currency from cli input
        $priceCurrency = $io->ask('Enter product price currency (default: EUR):');
        if ($priceCurrency == null) {
            $priceCurrency = 'EUR';
        }

        // create product dto
        $productDTO = new ProductDTO();
        $productDTO->name = $name;
        $productDTO->description = $description;
        $productDTO->price = (string) $price;
        $productDTO->priceCurrency = $priceCurrency;

        // validate input data
        $errors = $this->validator->validate($productDTO);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::INVALID;
        }

        // create new product
        try {
            $this->productManager->createProduct(
                $productDTO->name,
                $productDTO->description,
                $productDTO->price,
                $productDTO->priceCurrency
            );
            $io->success('Product: ' . $name . ' created.');
        } catch (Exception $e) {
            $io->error('Error to create product: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
