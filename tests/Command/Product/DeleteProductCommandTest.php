<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Command\Product\DeleteProductCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DeleteProductCommandTest
 *
 * Test cases for delete product command
 *
 * @package App\Tests\Command\Product
 */
class DeleteProductCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private ProductManager & MockObject $productManager;
    private DeleteProductCommand $deleteProductCommand;

    protected function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);

        // create command instance
        $this->deleteProductCommand = new DeleteProductCommand($this->productManager);
        $this->commandTester = new CommandTester($this->deleteProductCommand);
    }

    /**
     * Test execute command with invalid product id format
     *
     * @return void
     */
    public function testExecuteCommandWithInvalidProductIdFormat(): void
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
     * Test execute command with non-existing product
     *
     * @return void
     */
    public function testExecuteCommandProductNotFound(): void
    {
        // simulate product not found
        $this->productManager->expects($this->once())->method('getProductById')->with(1)
            ->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id: 1 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful product delete
     *
     * @return void
     */
    public function testExecuteCommandSuccess(): void
    {
        // mock product entity
        $productMock = $this->createMock(Product::class);
        $productMock->method('getName')->willReturn('Sample Product');
        $this->productManager->expects($this->once())->method('getProductById')->with(1)
            ->willReturn($productMock);

        // expect product delete call
        $this->productManager->expects($this->once())->method('deleteProduct')->with(1);

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product assets deleted for product: Sample Product', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with failure in deleting product
     *
     * @return void
     */
    public function testExecuteCommandFailure(): void
    {
        // mock product entity
        $productMock = $this->createMock(Product::class);
        $productMock->method('getName')->willReturn('Sample Product');
        $this->productManager->expects($this->once())->method('getProductById')->with(1)
            ->willReturn($productMock);

        // expect product delete to throw exception
        $this->productManager->expects($this->once())->method('deleteProduct')->with(1)
            ->willThrowException(new Exception('Error deleting product assets'));

        // execute command
        $exitCode = $this->commandTester->execute(['id' => 1]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to delete product: Error deleting product assets', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
