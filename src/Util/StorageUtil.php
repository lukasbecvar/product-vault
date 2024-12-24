<?php

namespace App\Util;

use App\Manager\ErrorManager;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StorageUtil
 *
 * Util for managing storage resources
 *
 * @package App\Util
 */
class StorageUtil
{
    private AppUtil $appUtil;
    private ErrorManager $errorManager;

    public function __construct(AppUtil $appUtil, ErrorManager $errorManager)
    {
        $this->appUtil = $appUtil;
        $this->errorManager = $errorManager;
    }

    /**
     * Prepare storage directories
     *
     * @return void
     */
    public function prepereStorageDirectories(): void
    {
        $basePath = __DIR__ . '/../../storage/' . $this->appUtil->getEnvValue('APP_ENV');
        $fileTypes = ['icons', 'images'];
        foreach ($fileTypes as $fileType) {
            $storagePath = $basePath . '/' . $fileType;
            if (!file_exists($storagePath)) {
                mkdir($storagePath, recursive: true);
            }
        }
    }

    /**
     * Check if assets exist
     *
     * @param string $subPath The sub path (icons, images)
     * @param string $resourceName The resource name
     *
     * @return bool True if assets exist, false otherwise
     */
    public function checkIfAssetsExist(string $subPath, string $resourceName): bool
    {
        // check if resource type is valid
        if (!in_array($subPath, ['icons', 'images'])) {
            $this->errorManager->handleError(
                message: 'Invalid resource type: ' . $subPath,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // build storage resource path
        $resourcePath = __DIR__ . '/../../storage/' . $this->appUtil->getEnvValue('APP_ENV') . '/' . $subPath;

        // check if storage resource exists
        if (file_exists($resourcePath . '/' . $resourceName)) {
            return true;
        }

        return false;
    }

    /**
     * Create storage resource
     *
     * @param string $subPath The sub path (icons, images)
     * @param string $resourceName The resource name
     * @param string $resourceContent The resource content
     *
     * @return void
     */
    public function createStorageResource(string $subPath, string $resourceName, string $resourceContent): void
    {
        // check if resource type is valid
        if (!in_array($subPath, ['icons', 'images'])) {
            $this->errorManager->handleError(
                message: 'Invalid resource type: ' . $subPath,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // build storage resource path
        $resourcePath = __DIR__ . '/../../storage/' . $this->appUtil->getEnvValue('APP_ENV') . '/' . $subPath;

        // create storage resource directory
        if (!file_exists($resourcePath)) {
            mkdir($resourcePath, recursive: true);
        }

        // check if resource already exists
        if (file_exists($resourcePath . '/' . $resourceName)) {
            $this->errorManager->handleError(
                message: 'Resource already exists: ' . $resourceName,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // create storage resource
        file_put_contents($resourcePath . '/' . $resourceName, $resourceContent);
    }

    /**
     * Get storage resource
     *
     * @param string $subPath The sub path (icons, images)
     * @param string $resourceName The resource name
     *
     * @return string|null The resource content or null if resource not found or error
     */
    public function getStorageResource(string $subPath, string $resourceName): ?string
    {
        // check if resource type is valid
        if (!in_array($subPath, ['icons', 'images'])) {
            $this->errorManager->handleError(
                message: 'Invalid resource type: ' . $subPath,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // build storage resource path
        $resourcePath = __DIR__ . '/../../storage/' . $this->appUtil->getEnvValue('APP_ENV') . '/' . $subPath;

        // get storage resource
        if (file_exists($resourcePath . '/' . $resourceName)) {
            $resourceContent = file_get_contents($resourcePath . '/' . $resourceName);

            // check if resource content is valid
            if ($resourceContent === false) {
                $this->errorManager->handleError(
                    message: 'Error to get resource: ' . $resourceName,
                    code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return $resourceContent;
        }

        return null;
    }

    /**
     * Delete storage resource
     *
     * @param string $subPath The sub path (icons, images)
     * @param string $resourceName The resource name
     *
     * @return void
     */
    public function deleteStorageResource(string $subPath, string $resourceName): void
    {
        // check if resource type is valid
        if (!in_array($subPath, ['icons', 'images'])) {
            $this->errorManager->handleError(
                message: 'Invalid resource type: ' . $subPath,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // build storage resource path
        $resourcePath = __DIR__ . '/../../storage/' . $this->appUtil->getEnvValue('APP_ENV') . '/' . $subPath;

        // delete storage resource
        if (file_exists($resourcePath . '/' . $resourceName)) {
            unlink($resourcePath . '/' . $resourceName);
        }
    }
}
