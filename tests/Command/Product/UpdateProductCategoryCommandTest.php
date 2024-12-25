<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductManager;
use App\Manager\CategoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\UpdateProductCategoryCommand;

/**
 * Class UpdateProductCategoryCommandTest
 *
 * Test cases for updating product category command
 *
 * @package App\Tests\Command\Product
 */
class UpdateProductCategoryCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private UpdateProductCategoryCommand $command;
    private ProductManager & MockObject $productManager;
    private CategoryManager & MockObject $categoryManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->categoryManager = $this->createMock(CategoryManager::class);

        // init command instance
        $this->command = new UpdateProductCategoryCommand($this->productManager, $this->categoryManager);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test execute command without product id input
     *
     * @return void
     */
    public function testExecuteCommandWithoutProductId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('The --product option is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command when no category action is provided
     *
     * @return void
     */
    public function testExecuteCommandWhenCategoryActionIsMissing(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('No category action provided. Use --add or --remove.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with non-existing product
     *
     * @return void
     */
    public function testExecuteCommandWithProductNotFound(): void
    {
        // mock category action and product check
        $this->categoryManager->expects($this->once())->method('getCategoryById')->with('2')->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category not found: 2', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful category addition
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulCategoryAddition(): void
    {
        // mock get product and category
        $product = $this->createMock(Product::class);
        $category = $this->createMock(Category::class);

        $this->categoryManager->expects($this->once())->method('getCategoryById')->with('2')->willReturn($category);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);
        $this->productManager->expects($this->once())->method('assingCategoryToProduct')->with($product, $category);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category: ' . $category->getName() . ' added to product: ' . $product->getName() . '.', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with successful category removal
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulCategoryRemoval(): void
    {
        // mock get product and category
        $product = $this->createMock(Product::class);
        $category = $this->createMock(Category::class);

        $this->categoryManager->expects($this->once())->method('getCategoryById')->with('2')->willReturn($category);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);
        $this->productManager->expects($this->once())->method('removeCategoryFromProduct')->with($product, $category);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--remove' => '2',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Category: ' . $category->getName() . ' removed from product: ' . $product->getName() . '.', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with exception response
     *
     * @return void
     */
    public function testExecuteCommandWithExceptionResponse(): void
    {
        // mock category and product
        $category = $this->createMock(Category::class);
        $product = $this->createMock(Product::class);

        // mock methods and throw exception
        $this->categoryManager->expects($this->once())->method('getCategoryById')->with('2')->willReturn($category);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);
        $this->productManager->expects($this->once())->method('assingCategoryToProduct')->willThrowException(new Exception('Database error'));

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error adding category to product: Database error', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
