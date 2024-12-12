<?php

namespace App\Manager;

use Exception;
use Predis\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CacheManager
 *
 * The manager for get and manage cache storage
 *
 * @package App\Manager
 */
class CacheManager
{
    private Client $redis;
    private ErrorManager $errorManager;

    public function __construct(Client $redis, ErrorManager $errorManager)
    {
        $this->redis = $redis;
        $this->errorManager = $errorManager;
    }

    /**
     * Save value to cache storage
     *
     * @param string $key The cache key
     * @param string $value The cache value
     * @param int $expirationTTL The cache expiration time in seconds
     *
     * @return void
     */
    public function saveCacheValue(string $key, string $value, int $expirationTTL = 60): void
    {
        try {
            $this->redis->set($key, $value, 'EX', $expirationTTL);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to save cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Check if cache value exists in storage
     *
     * @param string $key The cache key
     *
     * @return bool True if cache value exists, false otherwise
     */
    public function checkIsCacheValueExists(string $key): bool
    {
        try {
            $status = $this->redis->exists($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to check cache value exists',
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
     * Get cache value from storage
     *
     * @param string $key The cache key
     *
     * @return string|null The cache value if exists, null otherwise
     */
    public function getCacheValue(string $key): ?string
    {
        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to get cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Delete cache value from storage
     *
     * @param string $key The cache key
     *
     * @return void
     */
    public function deleteCacheValue(string $key): void
    {
        try {
            $this->redis->del($key);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to delete cache value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
