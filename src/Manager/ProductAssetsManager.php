<?php

namespace App\Manager;

use Exception;
use App\Entity\Product;
use App\Util\StorageUtil;
use App\Entity\ProductIcon;
use App\Entity\ProductImage;
use Symfony\Component\String\ByteString;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductIconRepository;
use App\Repository\ProductImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductAssetsManager
 *
 * Manager for manipulating with product assets
 *
 * @package App\Manager
 */
class ProductAssetsManager
{
    private LogManager $logManager;
    private StorageUtil $storageUtil;
    private ErrorManager $errorManager;
    private EntityManagerInterface $entityManager;
    private ProductIconRepository $productIconRepository;
    private ProductImageRepository $productImageRepository;

    public function __construct(
        LogManager $logManager,
        StorageUtil $storageUtil,
        ErrorManager $errorManager,
        EntityManagerInterface $entityManager,
        ProductIconRepository $productIconRepository,
        ProductImageRepository $productImageRepository
    ) {
        $this->logManager = $logManager;
        $this->storageUtil = $storageUtil;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->productIconRepository = $productIconRepository;
        $this->productImageRepository = $productImageRepository;
    }

    /**
     * Get product icons list
     *
     * @return array<ProductIcon> The product icons list
     */
    public function getProductIconsList(): array
    {
        return $this->productIconRepository->findAll();
    }

    /**
     * Get product icon by id
     *
     * @param int $id The product icon id
     *
     * @return ProductIcon|null The product icon object or null if product icon not found
     */
    public function getProductIconById(int $id): ?ProductIcon
    {
        return $this->productIconRepository->find($id);
    }

    /**
     * Get product icon by file name
     *
     * @param string $fileName The product icon file name
     *
     * @return ProductIcon|null The product icon object or null if product icon not found
     */
    public function getProductIconByFileName(string $fileName): ?ProductIcon
    {
        return $this->productIconRepository->findOneBy(['icon_file' => $fileName]);
    }

    /**
     * Create product icon
     *
     * @param string $iconPath The product icon file path
     * @param Product $product The product entity associated with icon
     *
     * @return void
     */
    public function createProductIcon(string $iconPath, Product $product): void
    {
        // icon file data
        $fileName = basename($iconPath);

        // generate unique icon file name
        $fileName = $this->generateAssetName('icons', $fileName);

        // create icon entity
        $icon = new ProductIcon();
        $icon->setIconFile($fileName);
        $icon->setProduct($product);

        try {
            // save icon entity to database
            $this->entityManager->persist($icon);
            $this->entityManager->flush();

            // create icon file
            $this->storageUtil->createStorageResource('icons', $fileName, $iconPath);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product icon created: ' . $fileName,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete product icon
     *
     * @param int $id The product icon id
     *
     * @return void
     */
    public function deleteProductIcon(int $id): void
    {
        // get icon by id
        $icon = $this->productIconRepository->find($id);

        // check if icon found
        if ($icon === null) {
            $this->errorManager->handleError(
                message: 'Product icon not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get icon file
        $iconFile = $icon->getIconFile();

        // check if icon file exists
        if ($iconFile === null) {
            $this->errorManager->handleError(
                message: 'Product icon file not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete icon
        try {
            $this->entityManager->remove($icon);
            $this->entityManager->flush();

            // delete icon file
            $this->storageUtil->deleteStorageResource('icons', $iconFile);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product icon deleted: ' . $icon->getIconFile(),
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Get product images list
     *
     * @return array<ProductImage> The product images list
     */
    public function getProductImagesList(): array
    {
        return $this->productImageRepository->findAll();
    }

    /**
     * Get product image by id
     *
     * @param int $id The product image id
     *
     * @return ProductImage|null The product image object or null if product image not found
     */
    public function getProductImageById(int $id): ?ProductImage
    {
        return $this->productImageRepository->find($id);
    }

    /**
     * Get product image by file name
     *
     * @param string $fileName The product image file name
     *
     * @return ProductImage|null The product image object or null if product image not found
     */
    public function getProductImageByFileName(string $fileName): ?ProductImage
    {
        return $this->productImageRepository->findOneBy(['image_file' => $fileName]);
    }

    /**
     * Create product image
     *
     * @param string $imagePath The product image file path
     * @param Product $product The product entity associated with image
     *
     * @return void
     */
    public function createProductImage(string $imagePath, Product $product): void
    {
        // image file data
        $fileName = basename($imagePath);

        // generate unique image file name
        $fileName = $this->generateAssetName('images', $fileName);

        // create image entity
        $image = new ProductImage();
        $image->setImageFile($fileName);
        $image->setProduct($product);

        try {
            // save image entity to database
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            // create image file
            $this->storageUtil->createStorageResource('images', $fileName, $imagePath);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create product image',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product image created: ' . $fileName,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete product image
     *
     * @param int $id The product image id
     *
     * @return void
     */
    public function deleteProductImage(int $id): void
    {
        // get image by id
        $image = $this->productImageRepository->find($id);

        // check if image found
        if ($image === null) {
            $this->errorManager->handleError(
                message: 'Product image not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get image file
        $imageFile = $image->getImageFile();

        // check if image file exists
        if ($imageFile === null) {
            $this->errorManager->handleError(
                message: 'Product image file not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete image
        try {
            $this->entityManager->remove($image);
            $this->entityManager->flush();

            // delete image file
            $this->storageUtil->deleteStorageResource('images', $imageFile);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete product image',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product image deleted: ' . $image->getImageFile(),
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Generate asset name
     *
     * @param string $subPath The sub path (icons, images)
     * @param string $resourceName The resource name
     *
     * @return string The new asset name
     */
    public function generateAssetName(string $subPath, string $resourceName): string
    {
        // get resource extension
        $extension = pathinfo($resourceName, PATHINFO_EXTENSION);

        do {
            // generate new name
            $newName = ByteString::fromRandom(16)->toString();

            // add extension if exists
            if (!empty($extension)) {
                $newName .= '.' . $extension;
            }
        } while ($this->storageUtil->checkIfAssetsExist($subPath, $newName));

        // return final unique asset name
        return $newName;
    }
}