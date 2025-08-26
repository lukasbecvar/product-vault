<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductManager;
use App\Manager\ProductAssetsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use App\Command\Product\DeleteProductImageCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DeleteProductImageCommandTest
 *
 * Test cases for deleting product image command
 *
 * @package App\Tests\Command\Product
 */
class DeleteProductImageCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private DeleteProductImageCommand $command;
    private ProductManager & MockObject $productManager;
    private ProductAssetsManager & MockObject $productAssetsManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->productAssetsManager = $this->createMock(ProductAssetsManager::class);

        // init command instance
        $this->command = new DeleteProductImageCommand($this->productManager, $this->productAssetsManager);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test command execution without product ID
     *
     * @return void
     */
    public function testExecuteCommandWithoutProductId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute(['--image' => '1']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('The --product option is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test command execution without image ID
     *
     * @return void
     */
    public function testExecuteCommandWithoutImageId(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute(['--product' => '1']);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('The --image option is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test command execution with non-existent product
     *
     * @return void
     */
    public function testExecuteCommandWithNonExistentProduct(): void
    {
        // mock product not found
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--image' => '1'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id: 1 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test command execution with successful image deletion
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulImageDeletion(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // mock product have image
        $this->productAssetsManager->expects($this->once())->method('checkIfProductHaveImage')->with($product, '1')
            ->willReturn(true);

        // expect delete product image call
        $this->productAssetsManager->expects($this->once())->method('deleteProductImage')->with('1');

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--image' => '1'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product image deleted: 1', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test command execution with exception during image deletion
     *
     * @return void
     */
    public function testExecuteCommandWithException(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // mock product have image
        $this->productAssetsManager->expects($this->once())->method('checkIfProductHaveImage')->with($product, '1')
            ->willReturn(true);

        // mock throw exception
        $this->productAssetsManager->expects($this->once())->method('deleteProductImage')
            ->with('1')->willThrowException(new Exception('Failed to delete image'));

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--image' => '1'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to delete product image: Failed to delete image', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
