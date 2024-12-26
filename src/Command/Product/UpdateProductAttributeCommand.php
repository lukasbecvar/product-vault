<?php

namespace App\Command\Product;

use Exception;
use App\Manager\ProductManager;
use App\Manager\AttributeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateProductAttributeValueCommand
 *
 * Command for updating product attributes
 *
 * @package App\Command\Product
 */
#[AsCommand(name: 'app:product:attribute:update', description: 'Update product attribute')]
class UpdateProductAttributeCommand extends Command
{
    private ProductManager $productManager;
    private AttributeManager $attributeManager;

    public function __construct(ProductManager $productManager, AttributeManager $attributeManager)
    {
        parent::__construct();
        $this->productManager = $productManager;
        $this->attributeManager = $attributeManager;
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
            ->addOption('add', null, InputOption::VALUE_REQUIRED, 'Attribute id to add')
            ->addOption('remove', null, InputOption::VALUE_REQUIRED, 'Attribute id to remove')
            ->addOption('value', null, InputOption::VALUE_REQUIRED, 'Attribute value (only for add)')
            ->setHelp(<<<'HELP'
                The <info>%command.name%</info> command allows you to add or remove attributes to a product:

                Examples:
                - Add an attribute:
                    <info>php bin/console %command.name% --product="1" --add="2" --value="3"</info>
                
                - Remove an attribute:
                    <info>php bin/console %command.name% --product="1" --remove="2"</info>

                - If no attribute action (--add or --remove) is provided, the command will display an error.

                Options:
                --product  (required) Product id
                --add      (optional) Attribute id to add
                --remove   (optional) Attribute id to remove
                --value    (optional) Attribute value (only for add)
            HELP);
    }

    /**
     * Execute product attribute update command
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

        // get command options
        $productId = $input->getOption('product');
        $attributeIdToAdd = $input->getOption('add');
        $attributeIdToRemove = $input->getOption('remove');
        $attributeValue = $input->getOption('value');

        // check if product id is provided
        if (!$productId) {
            $io->error('The --product option is required.');
            return Command::INVALID;
        }

        // check if attribute id to add or remove is provided
        if (!$attributeIdToAdd && !$attributeIdToRemove) {
            $io->warning('No attribute action provided. Use --add or --remove.');
            return Command::INVALID;
        }

        // assign attribute to product
        if ($attributeIdToAdd != null) {
            // get attribute by id
            $attribute = $this->attributeManager->getAttributeById($attributeIdToAdd);
            if ($attribute == null) {
                $io->error('Attribute not found: ' . $attributeIdToAdd);
                return Command::INVALID;
            }

            // get product by id
            $product = $this->productManager->getProductById($productId);
            if ($product == null) {
                $io->error('Product not found: ' . $productId);
                return Command::INVALID;
            }

            // validate attribute value
            if ($attributeValue == null) {
                $io->error('The --value option is required.');
                return Command::INVALID;
            }

            try {
                $this->productManager->assignAttributeToProduct($product, $attribute, $attributeValue);
                $io->success('Attribute: ' . $attribute->getName() . ' added to product: ' . $product->getName() . '.');
            } catch (Exception $e) {
                $io->error('Error adding attribute to product: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        // remove attribute from product
        if ($attributeIdToRemove != null) {
            // get attribute by id
            $attribute = $this->attributeManager->getAttributeById($attributeIdToRemove);
            if ($attribute == null) {
                $io->error('Attribute not found: ' . $attributeIdToRemove);
                return Command::INVALID;
            }

            // get product by id
            $product = $this->productManager->getProductById($productId);
            if ($product == null) {
                $io->error('Product not found: ' . $productId);
                return Command::INVALID;
            }

            try {
                $this->productManager->removeAttributeFromProduct($product, $attribute);
                $io->success('Attribute: ' . $attribute->getName() . ' removed from product: ' . $product->getName() . '.');
            } catch (Exception $e) {
                $io->error('Error removing attribute from product: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
