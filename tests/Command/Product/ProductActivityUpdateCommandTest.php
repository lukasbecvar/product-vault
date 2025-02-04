<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\ProductActivityUpdateCommand;

/**
 * Class ProductActivityUpdateCommandTest
 *
 * Test cases for product activity update command
 *
 * @package App\Tests\Command\Product
 */
class ProductActivityUpdateCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private ProductManager & MockObject $productManager;
    private ProductActivityUpdateCommand $productActivityUpdateCommand;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);

        // init command instance
        $this->productActivityUpdateCommand = new ProductActivityUpdateCommand($this->productManager);
        $this->commandTester = new CommandTester($this->productActivityUpdateCommand);
    }

    /**
     * Test execute command with invalid product id format
     *
     * @return void
     */
    public function testExecuteCommandInvalidProductId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            'id' => 'invalid_id',
            'activity' => 'active'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Invalid product id format.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with invalid product activity status
     *
     * @return void
     */
    public function testExecuteCommandInvalidActivity(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            'id' => 1,
            'activity' => 'invalid_activity'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Invalid product activity status format (allowed: active, inactive).', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with non-existing product
     *
     * @return void
     */
    public function testExecuteCommandProductNotFound(): void
    {
        // mock the product manager to return null for the product id
        $this->productManager->expects($this->once())->method('getProductById')->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            'id' => 1,
            'activity' => 'active'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id: 1 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful product activity update
     *
     * @return void
     */
    public function testExecuteCommandSuccess(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getName')->willReturn('Sample Product');

        // mock the product manager to return a mock product
        $this->productManager->expects($this->once())->method('getProductById')->willReturn($productMock);

        // mock the product manager's method for activating the product
        $this->productManager->expects($this->once())->method('activateProduct')->with(1);

        // execute command
        $exitCode = $this->commandTester->execute([
            'id' => 1,
            'activity' => 'active'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product: Sample Product activity updated', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with failure in updating product activity
     *
     * @return void
     */
    public function testExecuteCommandFailure(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getName')->willReturn('Sample Product');

        // mock the product manager to return a mock product
        $this->productManager->expects($this->once())->method('getProductById')->willReturn($productMock);

        // mock the product manager's method to throw an exception during product activity update
        $this->productManager->expects($this->once())->method('activateProduct')->willThrowException(new Exception('Error updating product activity'));

        // execute command
        $exitCode = $this->commandTester->execute([
            'id' => 1,
            'activity' => 'active'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to update product activity: Error updating product activity', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
