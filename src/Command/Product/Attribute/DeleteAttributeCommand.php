<?php

namespace App\Command\Product\Attribute;

use Exception;
use App\Manager\AttributeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteAttributeCommand
 *
 * Command for deleting product attribute
 *
 * @package App\Command\Product\Attribute
 */
#[AsCommand(name: 'app:product:attribute:delete', description: 'Delete product attribute')]
class DeleteAttributeCommand extends Command
{
    private AttributeManager $attributeManager;

    public function __construct(AttributeManager $attributeManager)
    {
        $this->attributeManager = $attributeManager;
        parent::__construct();
    }

    /**
     * Configure command arguments and options
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Attribute name');
    }

    /**
     * Execute attribute delete command
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

        // get attribute name
        $name = $input->getArgument('name');

        // check if attribute name is set
        if ($name == null) {
            $io->error('Attribute name is required.');
            return Command::INVALID;
        }

        // get attribute by name
        $attribute = $this->attributeManager->getAttributeByName($name);

        // check if attribute found
        if ($attribute === null) {
            $io->error('Attribute not found with name: ' . $name);
            return Command::INVALID;
        }

        // get attribute id
        $id = $attribute->getId();

        // check if attribute id is set
        if ($id == null) {
            $io->error('Attribute id is required.');
            return Command::INVALID;
        }

        // delete attribute
        try {
            $this->attributeManager->deleteAttribute($id);
            $io->success('Attribute deleted successfully');
        } catch (Exception $e) {
            $io->error('Error to delete attribute: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
