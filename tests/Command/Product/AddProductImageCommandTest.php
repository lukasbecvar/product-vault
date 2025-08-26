<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductManager;
use App\Manager\ProductAssetsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use App\Command\Product\AddProductImageCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AddProductImageCommandTest
 *
 * Test cases for adding product image command
 *
 * @package App\Tests\Command\Product
 */
class AddProductImageCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private AddProductImageCommand $command;
    private ProductManager & MockObject $productManager;
    private ProductAssetsManager & MockObject $productAssetsManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->productAssetsManager = $this->createMock(ProductAssetsManager::class);

        // init command instance
        $this->command = new AddProductImageCommand($this->productManager, $this->productAssetsManager);
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
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('The --product option is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test command execution without image path
     *
     * @return void
     */
    public function testExecuteCommandWithoutImagePath(): void
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
            '--image' => 'path/to/image.jpg'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product id: 1 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test command execution with successful image addition
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulImageAddition(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // expect create product image call
        $this->productAssetsManager->expects($this->once())->method('createProductImage')
            ->with('path/to/image.jpg', $product);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--image' => 'path/to/image.jpg'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product image added: path/to/image.jpg', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test command execution with exception during image addition
     *
     * @return void
     */
    public function testExecuteCommandWithException(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // mock throw exception
        $this->productAssetsManager->expects($this->once())->method('createProductImage')
            ->with('path/to/image.jpg', $product)->willThrowException(new Exception('Failed to add image'));

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--image' => 'path/to/image.jpg'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to add product image: Failed to add image', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
