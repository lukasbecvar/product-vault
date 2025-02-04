<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use App\Command\Product\EditProductCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class EditProductCommandTest
 *
 * Test cases for product edit command
 *
 * @package App\Tests\Command\Product
 */
class EditProductCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private ProductManager & MockObject $productManager;
    private EditProductCommand $editProductCommand;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);

        // create command instance
        $this->editProductCommand = new EditProductCommand($this->productManager);
        $this->commandTester = new CommandTester($this->editProductCommand);
    }

    /**
     * Test execute command with empty product id
     *
     * @return void
     */
    public function testExecuteCommandEmptyId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute(['id' => '']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id cannot be empty.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with invalid product id format
     *
     * @return void
     */
    public function testExecuteCommandInvalidId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute(['id' => 'invalid_id']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Invalid product id format.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with product not found
     *
     * @return void
     */
    public function testExecuteCommandProductNotFound(): void
    {
        // simulate product not found (return null from the mock)
        $this->productManager->method('getProductById')->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id: 1 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful product edit
     *
     * @return void
     */
    public function testExecuteCommandSuccess(): void
    {
        // prepare mock product entity
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Old Product Name');

        // simulate successful product retrieval
        $this->productManager->method('getProductById')->willReturn($product);

        // simulate user inputs during the command interaction
        $this->commandTester->setInputs([
            'Updated Product Name',  // Name
            'Updated description',   // Description
            '200',                   // Price
            'USD'                    // Currency
        ]);

        // mock the product manager to expect the edit operation
        $this->productManager->expects($this->once())->method('editProduct')->with(
            1,                       // ID
            'Updated Product Name',  // Name
            'Updated description',   // Description
            '200',                   // Price
            'USD'                    // Currency
        );

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product: Old Product Name edited', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with failure during product edit
     *
     * @return void
     */
    public function testExecuteCommandFailure(): void
    {
        // prepare mock product entity
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Old Product Name');

        // simulate product retrieval
        $this->productManager->method('getProductById')->willReturn($product);

        // simulate user inputs during the command interaction
        $this->commandTester->setInputs([
            'Updated Product Name',  // Name
            'Updated description',   // Description
            '200',                   // Price
            'USD'                    // Currency
        ]);

        // simulate an exception during product edit
        $this->productManager->expects($this->once())->method('editProduct')->willThrowException(new Exception('Database error'));

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to edit product: Database error', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
