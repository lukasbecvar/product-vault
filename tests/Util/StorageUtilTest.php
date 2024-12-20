<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use App\Util\StorageUtil;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\ByteString;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StorageUtilTest
 *
 * Test cases for storage util
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
        // ,ock dependencies
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create the StorageUtil instance with mocks
        $this->storageUtil = new StorageUtil($this->appUtilMock, $this->errorManagerMock);
    }

    /**
     * Test create storage resource with invalid sub path and resource name
     *
     * @return void
     */
    public function testCreateStorageResourceWithInvalidSubPath(): void
    {
        $fileName = ByteString::fromRandom(10);

        // mock storage env
        $this->appUtilMock->method('getEnvValue')->willReturn('test');

        // expect error handler call
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            'Invalid resource type: invalid_sub_path',
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );

        // call tested method
        $this->storageUtil->createStorageResource('invalid_sub_path', $fileName, 'resource content');
    }

    /**
     * Test create storage resource with invalid resource name
     *
     * @return void
     */
    public function testCreateStorageSuccess(): void
    {
        $fileName = ByteString::fromRandom(10);

        // mock storage env
        $this->appUtilMock->method('getEnvValue')->willReturn('test');

        // simulate a valid resource path
        $resourcePath = __DIR__ . '/../../storage/test/icons/' . $fileName;
        file_put_contents($resourcePath, 'resource content');

        // call tested method
        $result = $this->storageUtil->getStorageResource('icons', $fileName);

        // assert result
        $this->assertEquals('resource content', $result);
    }

    /**
     * Test create storage resource with empty resource name
     *
     * @return void
     */
    public function testDeleteStorageResource(): void
    {
        // mock storage env
        $this->appUtilMock->method('getEnvValue')->willReturn('test_env');

        // simulate a valid resource path
        $resourcePath = __DIR__ . '/../../storage/test_env/icons/test_file';
        file_put_contents($resourcePath, 'resource content');

        // expect unlink to be called once
        $this->expectOutputString('');

        // call tested method
        $this->storageUtil->deleteStorageResource('icons', 'test_file');
    }
}
