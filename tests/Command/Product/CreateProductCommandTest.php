<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use App\Command\Product\CreateProductCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateProductCommandTest
 *
 * Test cases for product create command
 *
 * @package App\Tests\Command\Product
 */
class CreateProductCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private ProductManager & MockObject $productManager;
    private CreateProductCommand $createProductCommand;
    private ValidatorInterface & MockObject $validator;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        // init command instance
        $this->createProductCommand = new CreateProductCommand($this->productManager, $this->validator);
        $this->commandTester = new CommandTester($this->createProductCommand);
    }

    /**
     * Test execute command with empty product name
     *
     * @return void
     */
    public function testExecuteCommandEmptyName(): void
    {
        // simulate empty name input
        $this->commandTester->setInputs(['', 'Sample description', '100', 'USD']);

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product name cannot be empty.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with invalid product price
     *
     * @return void
     */
    public function testExecuteCommandInvalidPrice(): void
    {
        // simulate invalid price input
        $this->commandTester->setInputs(['Sample Product', 'Sample description', 'invalid_price', 'USD']);

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Invalid product price format.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful product creation
     *
     * @return void
     */
    public function testExecuteCommandSuccess(): void
    {
        // simulate valid input
        $this->commandTester->setInputs(['Sample Product', 'Sample description', '100', 'USD']);

        // mock successful product creation
        $this->productManager->expects($this->once())->method('createProduct')->with(
            'Sample Product',
            'Sample description',
            '100',
            'USD'
        )->willReturn($this->createMock(Product::class));

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Product: Sample Product created', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with product creation failure
     *
     * @return void
     */
    public function testExecuteCommandFailure(): void
    {
        // simulate valid input
        $this->commandTester->setInputs(['Sample Product', 'Sample description', '100', 'USD']);

        // mock exception during product creation
        $this->productManager->expects($this->once())->method('createProduct')->willThrowException(new Exception('Database error'));

        // execute command
        $exitCode = $this->commandTester->execute([]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to create product: Database error', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
