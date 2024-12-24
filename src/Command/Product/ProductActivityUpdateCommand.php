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
 * Class ProductActivityUpdateCommand
 *
 * Command for updating product activity
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:activity:update', description: 'Update product activity')]
class ProductActivityUpdateCommand extends Command
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
        $this->addArgument('activity', InputArgument::REQUIRED, 'New product activity status');
    }

    /**
     * Execute product activity update command
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

        // validate product id input
        if (!is_numeric($id)) {
            $io->error('Invalid product id format.');
            return Command::INVALID;
        }

        // cast product id to integer
        $id = (int) $id;

        // get product activity status from cli input
        $activity = $input->getArgument('activity');

        // validate product activity status input
        if ($activity != 'active' && $activity != 'inactive') {
            $io->error('Invalid product activity status format (allowed: active, inactive).');
            return Command::INVALID;
        }

        // get product to update by id
        $productToEdit = $this->productManager->getProductById($id);

        // check if product id exists
        if ($productToEdit == null) {
            $io->error('Product not found: ' . $id);
            return Command::INVALID;
        }

        // update product activity
        try {
            if ($activity == 'active') {
                $this->productManager->activateProduct($id);
            } else {
                $this->productManager->deactivateProduct($id);
            }
            $io->success('Product: ' . $productToEdit->getName() . ' activity updated');
        } catch (Exception $e) {
            $io->error('Error to update product activity: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
