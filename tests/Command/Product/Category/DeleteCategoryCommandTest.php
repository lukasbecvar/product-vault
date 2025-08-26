<?php

namespace App\Tests\Command\Product\Category;

use Exception;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use App\Manager\CategoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\Category\DeleteCategoryCommand;

/**
 * Class DeleteCategoryCommandTest
 *
 * Test cases for delete category command
 *
 * @package App\Tests\Command\Product\Category
 */
class DeleteCategoryCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private DeleteCategoryCommand $deleteCategoryCommand;
    private CategoryManager & MockObject $categoryManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->categoryManager = $this->createMock(CategoryManager::class);

        // init command instance
        $this->deleteCategoryCommand = new DeleteCategoryCommand($this->categoryManager);
        $this->commandTester = new CommandTester($this->deleteCategoryCommand);
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
     * Test execute command with non-existent category
     *
     * @return void
     */
    public function testExecuteCommandWithNonExistentCategory(): void
    {
        // mock checkIfCategoryNameAlreadyExists method to return false
        $this->categoryManager->method('checkIfCategoryNameAlreadyExists')
            ->willReturn(false);

        // execute command with non-existent category name
        $exitCode = $this->commandTester->execute(['name' => 'NonExistentCategory']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category: NonExistentCategory not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with category name but not found in the database
     *
     * @return void
     */
    public function testExecuteCommandWithCategoryNotFound(): void
    {
        // mock checkIfCategoryNameAlreadyExists method to return true
        $this->categoryManager->method('checkIfCategoryNameAlreadyExists')->willReturn(true);

        // mock getCategoryByName method to return null
        $this->categoryManager->method('getCategoryByName')->willReturn(null);

        // execute command with valid but non-existent category name
        $exitCode = $this->commandTester->execute(['name' => 'NonExistentCategory']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category: NonExistentCategory not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with category found and deleted successfully
     *
     * @return void
     */
    public function testExecuteCommandWithCategoryDeletedSuccessfully(): void
    {
        // mock category object
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        // mock methods to return valid data
        $this->categoryManager->method('checkIfCategoryNameAlreadyExists')->willReturn(true);
        $this->categoryManager->method('getCategoryByName')->willReturn($categoryMock);

        // mock deleteCategory method to succeed
        $this->categoryManager->expects($this->once())->method('deleteCategory')->with(1);

        // execute command with valid category name
        $exitCode = $this->commandTester->execute(['name' => 'ValidCategory']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category deleted successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error during category deletion
     *
     * @return void
     */
    public function testExecuteCommandWithErrorDuringCategoryDeletion(): void
    {
        // mock category object
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        // mock methods to return valid data
        $this->categoryManager->method('checkIfCategoryNameAlreadyExists')->willReturn(true);
        $this->categoryManager->method('getCategoryByName')->willReturn($categoryMock);

        // simulate exception during category deletion
        $this->categoryManager->expects($this->once())->method('deleteCategory')
            ->will($this->throwException(new Exception('Error to delete category')));

        // execute command with valid category name
        $exitCode = $this->commandTester->execute(['name' => 'ValidCategory']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to delete category: Error to delete category', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
