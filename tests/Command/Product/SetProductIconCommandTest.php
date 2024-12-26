<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductManager;
use App\Manager\ProductAssetsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use App\Command\Product\SetProductIconCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class SetProductIconCommandTest
 *
 * Test cases for setting product icon command
 *
 * @package App\Tests\Command\Product
 */
class SetProductIconCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private SetProductIconCommand $command;
    private ProductManager & MockObject $productManager;
    private ProductAssetsManager & MockObject $productAssetsManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->productAssetsManager = $this->createMock(ProductAssetsManager::class);

        // init command instance
        $this->command = new SetProductIconCommand($this->productManager, $this->productAssetsManager);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test execute command without product id
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
     * Test execute command without icon path
     *
     * @return void
     */
    public function testExecuteCommandWithoutIconPath(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('The --icon option is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with non-existing product
     *
     * @return void
     */
    public function testExecuteCommandWithProductNotFound(): void
    {
        // mock product not found
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--icon' => 'path/to/icon.png',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product not found: 1', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful icon setting
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulIconSetting(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // expect create product icon call
        $this->productAssetsManager->expects($this->once())->method('createProductIcon')
            ->with('path/to/icon.png', $product);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--icon' => 'path/to/icon.png',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product icon set: path/to/icon.png', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with exception response
     *
     * @return void
     */
    public function testExecuteCommandWithExceptionResponse(): void
    {
        // mock product
        $product = $this->createMock(Product::class);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')
            ->willReturn($product);

        // mock throw exception
        $this->productAssetsManager->expects($this->once())->method('createProductIcon')
            ->with('path/to/icon.png', $product)
            ->willThrowException(new Exception('Failed to set icon'));

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--icon' => 'path/to/icon.png',
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to set product icon: Failed to set icon', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
