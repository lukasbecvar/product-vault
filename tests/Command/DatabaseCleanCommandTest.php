<?php

namespace App\Tests\Command;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Command\DatabaseCleanCommand;
use App\Repository\CategoryRepository;
use App\Repository\AttributeRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DatabaseCleanCommandTest
 *
 * Test cases for database clean command
 *
 * @package App\Tests\Command
 */
class DatabaseCleanCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private DatabaseCleanCommand $command;
    private CategoryRepository & MockObject $categoryRepository;
    private AttributeRepository & MockObject $attributeRepository;

    protected function setUp(): void
    {
        // mock dependencies
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->attributeRepository = $this->createMock(AttributeRepository::class);

        // create command instance
        $this->command = new DatabaseCleanCommand($this->categoryRepository, $this->attributeRepository);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test execute command with no unused entities
     *
     * @return void
     */
    public function testExecuteWithNoUnusedEntities(): void
    {
        // mock repository behavior
        $this->categoryRepository->expects($this->once())->method('removeUnusedCategories')->willReturn(0);
        $this->attributeRepository->expects($this->once())->method('removeUnusedAttributes')->willReturn(0);

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('No unused categories found', $output);
        $this->assertStringContainsString('No unused attributes found', $output);
        $this->assertStringContainsString('Database structure cleaned', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with unused entities
     *
     * @return void
     */
    public function testExecuteWithUnusedEntities(): void
    {
        // mock repository behavior
        $this->categoryRepository->expects($this->once())->method('removeUnusedCategories')->willReturn(5);
        $this->attributeRepository->expects($this->once())->method('removeUnusedAttributes')->willReturn(3);

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringNotContainsString('No unused categories found', $output);
        $this->assertStringNotContainsString('No unused attributes found', $output);
        $this->assertStringContainsString('Database structure cleaned', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error
     *
     * @return void
     */
    public function testExecuteWithError(): void
    {
        // mock repository behavior to throw an exception
        $this->categoryRepository->expects($this->once())->method('removeUnusedCategories')
            ->willThrowException(new Exception('Database error'));

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error cleaning database structure: Database error', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
