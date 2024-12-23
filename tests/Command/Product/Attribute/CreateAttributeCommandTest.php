<?php

namespace App\Tests\Command\Product\Attribute;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Manager\AttributeManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\Product\Attribute\CreateAttributeCommand;

/**
 * Class CreateAttributeCommandTest
 *
 * Test cases for create attribute command
 *
 * @package App\Tests\Command\Product\Attribute
 */
class CreateAttributeCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private CreateAttributeCommand $createAttributeCommand;
    private AttributeManager & MockObject $attributeManager;

    protected function setUp(): void
    {
        // mock dependencies
        $this->attributeManager = $this->createMock(AttributeManager::class);

        // init command instance
        $this->createAttributeCommand = new CreateAttributeCommand($this->attributeManager);
        $this->commandTester = new CommandTester($this->createAttributeCommand);
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
     * Test execute command with valid attribute name
     *
     * @return void
     */
    public function testExecuteCommandWithValidAttributeName(): void
    {
        // mock createAttribute method
        $this->attributeManager->expects($this->once())->method('createAttribute')
            ->with('new-attribute');

        // execute command with valid name
        $exitCode = $this->commandTester->execute([
            'name' => 'new-attribute'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Attribute created successfully', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    /**
     * Test execute command with error during attribute creation
     *
     * @return void
     */
    public function testExecuteCommandWithErrorDuringAttributeCreation(): void
    {
        // simulate exception during createAttribute
        $this->attributeManager->expects($this->once())->method('createAttribute')->with('new-attribute')
            ->will($this->throwException(new Exception('Failed to create attribute')));

        // execute command with valid name
        $exitCode = $this->commandTester->execute([
            'name' => 'new-attribute'
        ]);

        // get command output
        $output = $this->commandTester->getDisplay();

        // assert response
        $this->assertStringContainsString('Error to create attribute: Failed to create attribute', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }
}
