<?php

namespace App\Manager;

use Exception;
use Predis\Client;
use App\Util\AppUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CacheManager
 *
 * Manager for manipulating with cache storage
 *
 * @package App\Manager
 */
class CacheManager
{
    private Client $redis;
    private AppUtil $appUtil;
    private ErrorManager $errorManager;

    public function __construct(Client $redis, AppUtil $appUtil, ErrorManager $errorManager)
    {
        $this->redis = $redis;
        $this->appUtil = $appUtil;
        $this->errorManager = $errorManager;
    }

    /**
     * Check if redis connection is ok
     *
     * @return bool True if redis connection is ok, false otherwise
     */
    public function isRedisConnected(): bool
    {
        try {
            $status = $this->redis->ping();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to check redis connection',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // check if redis connection is ok
        if ($status == 'PONG') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Store value in cache storage
     *
     * @param string $key The cache key
     * @param string $value The cache value
     * @param int $expirationTTL The cache expiration time in seconds
     *
     * @return void
     */
    public function saveCacheValue(string $key, string $value, int $expirationTTL = 60): void
    {
        // check if product data caching is enabled
        if ($this->appUtil->getEnvValue('CACHE_PRODUCT_DATA') === 'false' && str_starts_with($key, 'product')) {
            return;
        }

        try {
            $this->redis->set($key, $value, 'EX', $expirationTTL);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to save cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Check if key exists in cache storage
     *
     * @param string $key The cache key
     *
     * @return bool True if cache value exists, false otherwise
     */
    public function checkIsCacheValueExists(string $key): bool
    {
        // check if product data caching is enabled
        if ($this->appUtil->getEnvValue('CACHE_PRODUCT_DATA') === 'false' && str_starts_with($key, 'product')) {
            return false;
        }

        try {
            $status = $this->redis->exists($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to check cache value exists',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // check if cache value exists
        if ($status == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get cached value from storage
     *
     * @param string $key The cache key
     *
     * @return string|null The cache value if exists, null otherwise
     */
    public function getCacheValue(string $key): ?string
    {
        // check if product data caching is enabled
        if ($this->appUtil->getEnvValue('CACHE_PRODUCT_DATA') === 'false' && str_starts_with($key, 'product')) {
            return null;
        }

        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to get cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Delete cached item from storage
     *
     * @param string $key The cache key
     *
     * @return void
     */
    public function deleteCacheValue(string $key): void
    {
        // check if product data caching is enabled
        if ($this->appUtil->getEnvValue('CACHE_PRODUCT_DATA') === 'false' && str_starts_with($key, 'product')) {
            return;
        }

        try {
            $this->redis->del($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Invalidate all cache keys that start with a given prefix
     *
     * @param string $prefix The prefix to match cache keys
     *
     * @return void
     */
    public function invalidateAllKeysStartsWith(string $prefix): void
    {
        // check if product data caching is enabled
        if ($this->appUtil->getEnvValue('CACHE_PRODUCT_DATA') === 'false' && str_starts_with($prefix, 'product')) {
            return;
        }

        $cursor = 0;
        try {
            do {
                // scan for keys starting with the given prefix
                $result = $this->redis->scan($cursor, [
                    'MATCH' => $prefix . '*'
                ]);

                $cursor = $result[0]; // update cursor for next iteration
                $keys = $result[1];  // extract matching keys

                // delete found keys
                if (!empty($keys)) {
                    $this->redis->del(...$keys);
                }
            } while ($cursor != 0);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to invalidate cache keys',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
