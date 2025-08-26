<?php

namespace App\Tests\Command\Product;

use Exception;
use App\Entity\Product;
use App\Entity\Attribute;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductManager;
use App\Manager\AttributeManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\UpdateProductAttributeCommand;

/**
 * Class UpdateProductAttributeCommandTest
 *
 * Test cases for updating product attribute command
 *
 * @package App\Tests\Command\Product
 */
class UpdateProductAttributeCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private UpdateProductAttributeCommand $command;
    private ProductManager & MockObject $productManager;
    private AttributeManager & MockObject $attributeManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->productManager = $this->createMock(ProductManager::class);
        $this->attributeManager = $this->createMock(AttributeManager::class);

        // init command instance
        $this->command = new UpdateProductAttributeCommand($this->productManager, $this->attributeManager);
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
     * Test execute command when no attribute action is provided
     *
     * @return void
     */
    public function testExecuteCommandWhenNoActionProvided(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('No attribute action provided. Use --add or --remove.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with non-existing attribute
     *
     * @return void
     */
    public function testExecuteCommandWithNonExistingAttribute(): void
    {
        // simulate attribute not found
        $this->attributeManager->expects($this->once())->method('getAttributeById')->with('2')->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
            '--value' => 'some-value'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute: 2 not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with successful attribute addition
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulAttributeAddition(): void
    {
        // mock get product and attribute
        $product = $this->createMock(Product::class);
        $attribute = $this->createMock(Attribute::class);
        $this->attributeManager->expects($this->once())->method('getAttributeById')->with('2')->willReturn($attribute);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);

        // expect attribute assignment
        $this->productManager->expects($this->once())->method('assignAttributeToProduct')->with($product, $attribute, 'some-value');

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
            '--value' => 'some-value'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute: ' . $attribute->getName() . ' added to product: ' . $product->getName() . '.', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with successful attribute removal
     *
     * @return void
     */
    public function testExecuteCommandWithSuccessfulAttributeRemoval(): void
    {
        // mock get product and attribute
        $product = $this->createMock(Product::class);
        $attribute = $this->createMock(Attribute::class);
        $this->attributeManager->expects($this->once())->method('getAttributeById')->with('2')->willReturn($attribute);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);

        // expect attribute removal
        $this->productManager->expects($this->once())->method('removeAttributeFromProduct')->with($product, $attribute);

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--remove' => '2'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute: ' . $attribute->getName() . ' removed from product: ' . $product->getName() . '.', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with exception response
     *
     * @return void
     */
    public function testExecuteCommandWithException(): void
    {
        // mock get product and attribute
        $product = $this->createMock(Product::class);
        $attribute = $this->createMock(Attribute::class);
        $this->attributeManager->expects($this->once())->method('getAttributeById')->with('2')->willReturn($attribute);
        $this->productManager->expects($this->once())->method('getProductById')->with('1')->willReturn($product);

        // mock throw exception
        $this->productManager->expects($this->once())->method('assignAttributeToProduct')->willThrowException(new Exception('Database error'));

        // execute command
        $exitCode = $this->commandTester->execute([
            '--product' => '1',
            '--add' => '2',
            '--value' => 'some-value'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error adding attribute to product: Database error', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
