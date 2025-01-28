<?php

namespace App\Tests\Manager;

use App\Entity\Attribute;
use App\Manager\LogManager;
use App\Manager\CacheManager;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use App\Manager\AttributeManager;
use App\Repository\AttributeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AttributeManagerTest
 *
 * Test cases for attribute manager
 *
 * @package App\Test\Manager
 */
class AttributeManagerTest extends TestCase
{
    private AttributeManager $attributeManager;
    private LogManager & MockObject $logManager;
    private CacheManager & MockObject $cacheManager;
    private ErrorManager & MockObject $errorManager;
    private EntityManagerInterface & MockObject $entityManager;
    private AttributeRepository & MockObject $attributeRepository;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->cacheManager = $this->createMock(CacheManager::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->attributeRepository = $this->createMock(AttributeRepository::class);

        // create attribute manager instance
        $this->attributeManager = new AttributeManager(
            $this->logManager,
            $this->cacheManager,
            $this->errorManager,
            $this->entityManager,
            $this->attributeRepository
        );
    }

    /**
     * Test check if attribute name already exists
     *
     * @return void
     */
    public function testCheckIfAttributeNameAlreadyExists(): void
    {
        // simulate attribute exists
        $this->attributeRepository->method('findOneBy')
            ->with(['name' => 'Existing Attribute'])->willReturn(new Attribute());

        // call tested method
        $result = $this->attributeManager->checkIfAttributeNameAlreadyExists('Existing Attribute');

        // assert result
        $this->assertTrue($result);
    }

    /**
     * Test get attribute by id
     *
     * @return void
     */
    public function testGetAttributeById(): void
    {
        // mock attribute
        $attribute = new Attribute();
        $attribute->setName('Attribute 1');
        $this->attributeRepository->method('find')->with(1)->willReturn($attribute);

        // call tested method
        $result = $this->attributeManager->getAttributeById(1);

        // assert result
        $this->assertSame($attribute, $result);
    }

    /**
     * Test get attribute by name
     *
     * @return void
     */
    public function testGetAttributeByName(): void
    {
        // mock attribute
        $attribute = new Attribute();
        $attribute->setName('Attribute 1');
        $this->attributeRepository->method('findOneBy')
            ->with(['name' => 'Attribute 1'])->willReturn($attribute);

        // call tested method
        $result = $this->attributeManager->getAttributeByName('Attribute 1');

        // assert result
        $this->assertSame($attribute, $result);
    }

    /**
     * Test get attributes list
     *
     * @return void
     */
    public function testGetAttributesList(): void
    {
        // call tested method
        $result = $this->attributeManager->getAttributesList();

        // assert result
        $this->assertIsArray($result);
    }

    /**
     * Test create attribute
     *
     * @return void
     */
    public function testCreateAttribute(): void
    {
        // simulate attribute not exists
        $this->attributeRepository->method('findOneBy')->with(['name' => 'New Attribute'])
            ->willReturn(null);

        // expect entity persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Attribute::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Attribute created: New Attribute',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->attributeManager->createAttribute('New Attribute');
    }

    /**
     * Test create attribute already exists
     *
     * @return void
     */
    public function testCreateAttributeAlreadyExists(): void
    {
        // simulate attribute exists
        $this->attributeRepository->method('findOneBy')->with(['name' => 'Existing Attribute'])
            ->willReturn(new Attribute());

        // expect error handler call
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Attribute name: Existing Attribute already exists',
            JsonResponse::HTTP_CONFLICT
        );

        // call tested method
        $this->attributeManager->createAttribute('Existing Attribute');
    }

    /**
     * Test rename attribute
     *
     * @return void
     */
    public function testRenameAttribute(): void
    {
        // mock attribute
        $attribute = new Attribute();
        $attribute->setName('Old Name');
        $this->attributeRepository->method('find')->with(1)->willReturn($attribute);

        // simulate attribute new name not exists
        $this->attributeRepository->method('findOneBy')->with(['name' => 'New Name'])->willReturn(null);

        // expect entity flush
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Attribute renamed: New Name',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->attributeManager->renameAttribute(1, 'New Name');
    }

    /**
     * Test delete attribute
     *
     * @return void
     */
    public function testDeleteAttribute(): void
    {
        // mock attribute
        $attribute = new Attribute();
        $attribute->setName('Attribute to Delete');
        $this->attributeRepository->method('find')->with(1)->willReturn($attribute);

        // expect entity remove and flush
        $this->entityManager->expects($this->once())->method('remove')->with($attribute);
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Attribute deleted: Attribute to Delete',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->attributeManager->deleteAttribute(1);
    }
}
