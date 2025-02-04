<?php

namespace App\Tests\Manager;

use Predis\Client;
use App\Util\AppUtil;
use App\Manager\CacheManager;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CacheManagerTest
 *
 * Test cases for cache manager
 *
 * @package App\Tests\Manager
 */
class CacheManagerTest extends TestCase
{
    private CacheManager $cacheManager;
    private Client & MockObject $redis;
    private AppUtil & MockObject $appUtilMock;
    private ErrorManager & MockObject $errorManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->redis = $this->createMock(Client::class);
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->errorManager = $this->createMock(ErrorManager::class);

        // init cache manager instance
        $this->cacheManager = new CacheManager($this->redis, $this->appUtilMock, $this->errorManager);
    }

    /**
     * Test check is redis connected
     *
     * @return void
     */
    public function testCheckIsRedisConnected(): void
    {
        // expect call check redis connection
        $this->redis->expects($this->once())->method('__call')->with('ping');

        // call tested method
        $this->cacheManager->isRedisConnected();
    }

    /**
     * Test save cache value
     *
     * @return void
     */
    public function testSaveCacheValue(): void
    {
        // expect call set cache value
        $this->redis->expects($this->once())->method('__call')
            ->with('set', ['test_key', 'test_value', 'EX', 60]);

        // expect no error handling
        $this->errorManager->expects($this->never())->method('handleError');

        // call tested method
        $this->cacheManager->saveCacheValue('test_key', 'test_value');
    }

    /**
     * Test save cache value with error handling
     *
     * @return void
     */
    public function testCheckIsCacheValueExists(): void
    {
        // expect call check cache value exists
        $this->redis->expects($this->once())->method('__call')
            ->with('exists', ['test_key']);

        // expect no error handling
        $this->errorManager->expects($this->never())->method('handleError');

        // call tested method
        $this->cacheManager->checkIsCacheValueExists('test_key');
    }

    /**
     * Test check is cache value exists
     *
     * @return void
     */
    public function testGetCacheValue(): void
    {
        // expect call get cache value
        $this->redis->expects($this->once())->method('__call')
            ->with('get', ['test_key']);

        // expect no error handling
        $this->errorManager->expects($this->never())->method('handleError');

        // call tested method
        $this->cacheManager->getCacheValue('test_key');
    }

    /**
     * Test delete cache value
     *
     * @return void
     */
    public function testDeleteCacheValue(): void
    {
        // expect call delete cache value
        $this->redis->expects($this->once())->method('__call')
            ->with('del', ['test_key']);

        // expect no error handling
        $this->errorManager->expects($this->never())->method('handleError');

        // call tested method
        $this->cacheManager->deleteCacheValue('test_key');
    }

    /**
     * Test invalidate all keys that start with a given prefix
     *
     * @return void
     */
    public function testInvalidateAllKeysStartsWith(): void
    {
        // mock scan responses for iterations
        $scanResponses = [
            [1, ['prefix:key1', 'prefix:key2']],
            [0, []]
        ];

        // mock scan and del behavior
        $this->redis->method('__call')->willReturnCallback(function ($name, $arguments) use (&$scanResponses) {
            if ($name === 'scan') {
                return array_shift($scanResponses);
            } elseif ($name === 'del') {
                $this->assertEquals(['prefix:key1', 'prefix:key2'], $arguments);
                return null;
            }
            return null;
        });

        // call tested method
        $this->cacheManager->invalidateAllKeysStartsWith('prefix:');
    }
}
