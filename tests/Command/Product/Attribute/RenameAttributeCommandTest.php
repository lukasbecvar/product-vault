<?php

namespace App\Tests\Command\Product\Attribute;

use Exception;
use App\Entity\Attribute;
use PHPUnit\Framework\TestCase;
use App\Manager\AttributeManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\Attribute\RenameAttributeCommand;

/**
 * Class RenameAttributeCommandTest
 *
 * Test cases for rename attribute command
 *
 * @package App\Tests\Command\Product\Attribute
 */
class RenameAttributeCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private RenameAttributeCommand $renameAttributeCommand;
    private AttributeManager & MockObject $attributeManager;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->attributeManager = $this->createMock(AttributeManager::class);

        // Init command instance
        $this->renameAttributeCommand = new RenameAttributeCommand($this->attributeManager);
        $this->commandTester = new CommandTester($this->renameAttributeCommand);
    }

    /**
     * Test execute command without arguments
     *
     * @return void
     */
    public function testExecuteCommandWithoutArguments(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            'old-name' => '',
            'new-name' => ''
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute old-name is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command with missing new-name argument
     *
     * @return void
     */
    public function testExecuteCommandWithMissingNewName(): void
    {
        // execute command
        $exitCode = $this->commandTester->execute([
            'old-name' => 'old-name',
            'new-name' => ''
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute new-name is required.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command when attribute is not found
     *
     * @return void
     */
    public function testExecuteCommandWhenAttributeNotFound(): void
    {
        // mock get attribute by name
        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('old-name')
            ->willReturn(null);

        // execute command
        $exitCode = $this->commandTester->execute([
            'old-name' => 'old-name',
            'new-name' => 'new-name'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute: old-name not found.', $output);
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    /**
     * Test execute command when rename is successful
     *
     * @return void
     */
    public function testExecuteCommandWithValidRename(): void
    {
        // mock get attribute by old name
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getId')->willReturn(123);
        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('old-name')
            ->willReturn($attributeMock);

        // expect call attribute rename
        $this->attributeManager->expects($this->once())->method('renameAttribute')->with(123, 'new-name');

        // call tested method
        $exitCode = $this->commandTester->execute([
            'old-name' => 'old-name',
            'new-name' => 'new-name'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute renamed successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command when renaming fails
     *
     * @return void
     */
    public function testExecuteCommandWithRenameFailure(): void
    {
        // mock get attribute by old name
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getId')->willReturn(123);
        $this->attributeManager->expects($this->once())->method('getAttributeByName')->with('old-name')
            ->willReturn($attributeMock);

        // mock exception during rename
        $this->attributeManager->expects($this->once())->method('renameAttribute')->with(123, 'new-name')
            ->willThrowException(new Exception('Rename operation failed'));

        // execute command
        $exitCode = $this->commandTester->execute([
            'old-name' => 'old-name',
            'new-name' => 'new-name'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to rename attribute: Rename operation failed', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
