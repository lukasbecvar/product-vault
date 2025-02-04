<?php

namespace App\Tests\Command\Product\Attribute;

use Exception;
use App\Entity\Attribute;
use PHPUnit\Framework\TestCase;
use App\Manager\AttributeManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\Attribute\DeleteAttributeCommand;

/**
 * Class DeleteAttributeCommandTest
 *
 * Test cases for delete attribute command
 *
 * @package App\Tests\Command\Product\Attribute
 */
class DeleteAttributeCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private DeleteAttributeCommand $deleteAttributeCommand;
    private AttributeManager & MockObject $attributeManager;

    protected function setUp(): void
    {
        // mock dependencies
        $this->attributeManager = $this->createMock(AttributeManager::class);

        // init command instance
        $this->deleteAttributeCommand = new DeleteAttributeCommand($this->attributeManager);
        $this->commandTester = new CommandTester($this->deleteAttributeCommand);
    }

    /**
     * Test execute command without attribute name
     *
     * @return void
     */
    public function testExecuteCommandWithoutAttributeName(): void
    {
        // execute command without name argument
        $exitCode = $this->commandTester->execute([
            'name' => ''
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute name is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command when attribute not found
     *
     * @return void
     */
    public function testExecuteCommandWhenAttributeNotFound(): void
    {
        // mock getAttributeByName to return null
        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('non-existent-attribute')
            ->willReturn(null);

        // execute command with non-existent attribute name
        $exitCode = $this->commandTester->execute([
            'name' => 'non-existent-attribute'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute: non-existent-attribute not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with valid attribute
     *
     * @return void
     */
    public function testExecuteCommandWithValidAttribute(): void
    {
        // mock getAttributeByName and deleteAttribute methods
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getId')->willReturn(123);

        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('existing-attribute')
            ->willReturn($attributeMock);

        $this->attributeManager->expects($this->once())->method('deleteAttribute')->with(123);

        // execute command with valid attribute name
        $exitCode = $this->commandTester->execute([
            'name' => 'existing-attribute'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute deleted successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error during deletion
     *
     * @return void
     */
    public function testExecuteCommandWithErrorDuringDeletion(): void
    {
        // mock existing attribute
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getId')->willReturn(123);
        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('existing-attribute')
            ->willReturn($attributeMock);

        // simulate exception during deleteAttribute
        $this->attributeManager->expects($this->once())->method('deleteAttribute')->with(123)
            ->will($this->throwException(new Exception('Failed to delete attribute')));

        // execute command with valid attribute name
        $exitCode = $this->commandTester->execute([
            'name' => 'existing-attribute'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to delete attribute: Failed to delete attribute', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
