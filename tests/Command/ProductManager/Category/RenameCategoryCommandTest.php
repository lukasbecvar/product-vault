<?php

namespace App\Tests\Command\ProductManager\Category;

use Exception;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use App\Manager\CategoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ProductManager\Category\RenameCategoryCommand;

/**
 * Class RenameCategoryCommandTest
 *
 * Test cases for rename category command
 *
 * @package App\Tests\Command\ProductManager\Category
 */
class RenameCategoryCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private RenameCategoryCommand $renameCategoryCommand;
    private CategoryManager & MockObject $categoryManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->categoryManager = $this->createMock(CategoryManager::class);

        // init command instance
        $this->renameCategoryCommand = new RenameCategoryCommand($this->categoryManager);
        $this->commandTester = new CommandTester($this->renameCategoryCommand);
    }

    /**
     * Test execute command without category name
     *
     * @return void
     */
    public function testExecuteCommandWithoutCategoryName(): void
    {
        // execute command without name arguments
        $exitCode = $this->commandTester->execute([
            'old-name' => '',
            'new-name' => ''
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category name cannot be empty.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with missing new name
     *
     * @return void
     */
    public function testExecuteCommandWithoutNewCategoryName(): void
    {
        // execute command with old name but no new name
        $exitCode = $this->commandTester->execute([
            'old-name' => 'OldCategory',
            'new-name' => ''
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('New category name cannot be empty.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with non-existent old category
     *
     * @return void
     */
    public function testExecuteCommandWithNonExistentOldCategory(): void
    {
        // mock getCategoryByName method to return null
        $this->categoryManager->method('getCategoryByName')->willReturn(null);

        // execute command with non-existent old category name
        $exitCode = $this->commandTester->execute([
            'old-name' => 'NonExistentOldCategory',
            'new-name' => 'NewCategory'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category not found: NonExistentOldCategory', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with valid category and renaming successfully
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulRename(): void
    {
        // mock category object
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        // mock getCategoryByName to return the category
        $this->categoryManager->method('getCategoryByName')->willReturn($categoryMock);

        // mock renameCategory method to succeed
        $this->categoryManager->expects($this->once())->method('renameCategory')->with(1, 'NewCategory');

        // execute command with valid category names
        $exitCode = $this->commandTester->execute([
            'old-name' => 'OldCategory',
            'new-name' => 'NewCategory'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category renamed successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error during category renaming
     *
     * @return void
     */
    public function testExecuteCommandWithErrorDuringRename(): void
    {
        // mock category object
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        // mock getCategoryByName to return the category
        $this->categoryManager->method('getCategoryByName')->willReturn($categoryMock);

        // simulate exception during category renaming
        $this->categoryManager->expects($this->once())->method('renameCategory')
            ->will($this->throwException(new Exception('Error to rename category')));

        // execute command with valid category names
        $exitCode = $this->commandTester->execute([
            'old-name' => 'OldCategory',
            'new-name' => 'NewCategory'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to rename category: Error to rename category', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
