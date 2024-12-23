<?php

namespace App\Tests\Command\ProductManager\Category;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Manager\CategoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ProductManager\Category\CreateCategoryCommand;

/**
 * Class CreateCategoryCommandTest
 *
 * Test cases for create category command
 *
 * @package App\Tests\Command\ProductManager\Category
 */
class CreateCategoryCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private CreateCategoryCommand $createCategoryCommand;
    private CategoryManager & MockObject $categoryManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->categoryManager = $this->createMock(CategoryManager::class);

        // init command instance
        $this->createCategoryCommand = new CreateCategoryCommand($this->categoryManager);
        $this->commandTester = new CommandTester($this->createCategoryCommand);
    }

    /**
     * Test execute command without category name
     *
     * @return void
     */
    public function testExecuteCommandWithoutCategoryName(): void
    {
        // execute command without name argument
        $exitCode = $this->commandTester->execute(['name' => '']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category name is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with category name
     *
     * @return void
     */
    public function testExecuteCommandWithCategoryName(): void
    {
        // mock category creation
        $this->categoryManager->expects($this->once())->method('createCategory')
            ->with('New Category');

        // execute command with name argument
        $exitCode = $this->commandTester->execute(['name' => 'New Category']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error during category creation
     *
     * @return void
     */
    public function testExecuteCommandWithErrorDuringCategoryCreation(): void
    {
        // simulate exception during category creation
        $this->categoryManager->expects($this->once())->method('createCategory')
            ->will($this->throwException(new Exception('Error to create category')));

        // execute command with name argument
        $exitCode = $this->commandTester->execute(['name' => 'New Category']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to create category: Error to create category', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
