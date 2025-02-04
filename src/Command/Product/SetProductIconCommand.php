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
 * Class SetProductIconCommand
 *
 * Command for setting product icon
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:icon:set', description: 'Set product icon')]
class SetProductIconCommand extends Command
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
            ->addOption('icon', null, InputOption::VALUE_REQUIRED, 'Icon file path')
            ->setHelp(<<<'HELP'
                The <info>%command.name%</info> command allows you to set product icon:

                Examples:
                - Set product icon:
                    <info>php bin/console %command.name% --product="1" --icon="path/to/icon.png"</info>

                Options:
                --product  (required) Product id
                --icon     (required) Icon file path
            HELP);
    }

    /**
     * Execute product icon set command
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
        $iconPath = $input->getOption('icon');

        // check if product id is provided
        if (!$productId) {
            $io->error('The --product option is required.');
            return Command::INVALID;
        }

        // check if icon path is provided
        if (!$iconPath) {
            $io->error('The --icon option is required.');
            return Command::INVALID;
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $io->error('Product id: ' . $productId . ' not found.');
            return Command::INVALID;
        }

        // set product icon
        try {
            $this->productAssetsManager->createProductIcon($iconPath, $product);
            $io->success('Product icon set: ' . $iconPath . '.');
        } catch (Exception $e) {
            $io->error('Error to set product icon: ' . $e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
