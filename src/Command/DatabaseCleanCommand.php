<?php

namespace App\Command;

use Exception;
use App\Repository\CategoryRepository;
use App\Repository\AttributeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseCleanCommand
 *
 * Command for cleaning database structure
 *
 * @package App\Command
 */
#[AsCommand(name: 'app:database:clean', description: 'Clean database structure')]
class DatabaseCleanCommand extends Command
{
    private CategoryRepository $categoryRepository;
    private AttributeRepository $attributeRepository;

    public function __construct(CategoryRepository $categoryRepository, AttributeRepository $attributeRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
        parent::__construct();
    }

    /**
     * Execute database clean command
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

        try {
            // clean database structure
            $removedCategories = $this->categoryRepository->removeUnusedCategories();
            $removedAttributes = $this->attributeRepository->removeUnusedAttributes();

            // log result
            if ($removedCategories <= 0) {
                $io->warning('No unused categories found.');
            }
            if ($removedAttributes <= 0) {
                $io->warning('No unused attributes found.');
            }
            $io->success('Database structure cleaned!');
        } catch (Exception $e) {
            $io->error('Error cleaning database structure: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
