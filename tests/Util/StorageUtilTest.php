<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use App\Util\StorageUtil;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StorageUtilTest
 *
 * Test cases for storage resource manager util
 *
 * @package App\Tests\Util
 */
class StorageUtilTest extends TestCase
{
    private StorageUtil $storageUtil;
    private AppUtil & MockObject $appUtilMock;
    private ErrorManager & MockObject $errorManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create storage util instance
        $this->storageUtil = new StorageUtil($this->appUtilMock, $this->errorManagerMock);
    }

    /**
     * Test create storage resource with invalid sub path
     *
     * @return void
     */
    public function testCreateStorageResourceWithInvalidSubPath(): void
    {
        // mock APP_ENV
        $this->appUtilMock->method('getEnvValue')->willReturn('test');

        // expect error handling
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            $this->stringContains('Invalid resource type'),
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );

        // call test method
        $this->storageUtil->createStorageResource('invalid_type', 'test.txt', 'content');
    }

    /**
     * Test get storage resource with invalid sub path
     *
     * @return void
     */
    public function testGetStorageResourceWithInvalidSubPath(): void
    {
        // mock APP_ENV
        $this->appUtilMock->method('getEnvValue')->willReturn('test');

        // expect error handling
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            $this->stringContains('Invalid resource type'),
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );

        // call test method
        $this->storageUtil->getStorageResource('invalid_type', 'test.txt');
    }

    /**
     * Test get storage resource with invalid sub path
     *
     * @return void
     */
    public function testDeleteStorageResourceWithInvalidSubPath(): void
    {
        // mock APP_ENV
        $this->appUtilMock->method('getEnvValue')->willReturn('test');

        // expect error handling
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            $this->stringContains('Invalid resource type'),
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );

        // call test method
        $this->storageUtil->deleteStorageResource('invalid_type', 'test.txt');
    }
}
