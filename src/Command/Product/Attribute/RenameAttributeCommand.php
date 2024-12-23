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
 * Class RenameAttributeCommand
 *
 * Command for renaming product attribute
 *
 * @package App\Command\Product\Attribute
 */
#[AsCommand(name: 'app:product:attribute:rename', description: 'Rename product attribute')]
class RenameAttributeCommand extends Command
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
        $this->addArgument('old-name', InputArgument::REQUIRED, 'Attribute old name');
        $this->addArgument('new-name', InputArgument::REQUIRED, 'Attribute new name');
    }

    /**
     * Execute attribute rename command
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

        // get attribute old-name
        $oldName = $input->getArgument('old-name');

        // check if attribute old-name is set
        if ($oldName == null) {
            $io->error('Attribute old-name is required.');
            return Command::INVALID;
        }

        // get attribute name
        $newName = $input->getArgument('new-name');

        // check if attribute new-name is set
        if ($newName == null) {
            $io->error('Attribute new-name is required.');
            return Command::INVALID;
        }

        // get attribute by name
        $attribute = $this->attributeManager->getAttributeByName($oldName);

        // check if attribute found
        if ($attribute === null) {
            $io->error('Attribute not found with name: ' . $oldName);
            return Command::INVALID;
        }

        // get attribute id
        $id = $attribute->getId();

        // check if attribute id is set
        if ($id == null) {
            $io->error('Attribute id is required.');
            return Command::INVALID;
        }

        // rename attribute
        try {
            $this->attributeManager->renameAttribute($id, $newName);
            $io->success('Attribute renamed successfully');
        } catch (Exception $e) {
            $io->error('Error to rename attribute: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
