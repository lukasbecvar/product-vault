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
    private CacheManager $cacheManager;
    private ErrorManager $errorManager;
    private EntityManagerInterface $entityManager;
    private ProductIconRepository $productIconRepository;
    private ProductImageRepository $productImageRepository;

    public function __construct(
        LogManager $logManager,
        StorageUtil $storageUtil,
        CacheManager $cacheManager,
        ErrorManager $errorManager,
        EntityManagerInterface $entityManager,
        ProductIconRepository $productIconRepository,
        ProductImageRepository $productImageRepository
    ) {
        $this->logManager = $logManager;
        $this->storageUtil = $storageUtil;
        $this->cacheManager = $cacheManager;
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
     * Get product icon
     *
     * @param string $iconFile The product icon file name
     *
     * @return string The product icon content
     */
    public function getProductIcon(string $iconFile): string
    {
        $icon = $this->storageUtil->getStorageResource('icons', $iconFile);

        // check if icon exists
        if ($icon === null) {
            $this->errorManager->handleError(
                message: 'Product icon not found: ' . $iconFile,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $icon;
    }

    /**
     * Check if product have icon
     *
     * @param Product $product The product entity
     *
     * @return bool True if product have icon, false otherwise
     */
    public function checkIfProductHaveIcon(Product $product): bool
    {
        return $product->getIcon() !== null;
    }

    /**
     * Create product icon
     *
     * @param string $iconPath The product icon file path
     * @param Product $product The product entity associated with icon
     * @param string|null $iconExtension The icon file extension (use for files without extension)
     *
     * @return void
     */
    public function createProductIcon(string $iconPath, Product $product, ?string $iconExtension = null): void
    {
        // check if product already has icon (update icon)
        if ($product->getIcon() !== null) {
            $this->updateProductIcon($iconPath, $product);
            return;
        }

        // icon file data
        $fileName = basename($iconPath);

        // generate unique icon file name
        $fileName = $this->generateAssetName('icons', $fileName);

        // get icon file content
        $resourceContent = file_get_contents($iconPath);
        if ($resourceContent === false) {
            $this->errorManager->handleError(
                message: 'Error to create product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // apped icon extension if exists
        if ($iconExtension != null) {
            $fileName .= '.' . $iconExtension;
        }

        // create icon entity
        $icon = new ProductIcon();
        $icon->setIconFile($fileName);
        $icon->setProduct($product);

        try {
            // save icon entity to database
            $this->entityManager->persist($icon);
            $this->entityManager->flush();

            // create icon file
            $this->storageUtil->createStorageResource('icons', $fileName, $resourceContent);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // invalidate cache data
        $this->cacheManager->invalidateAllKeysStartsWith('product_' . $product->getId() . '_currency_');
        $this->cacheManager->invalidateAllKeysStartsWith('product_list_search_');

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product icon created: ' . $fileName,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Update product icon
     *
     * @param string $iconPath The product icon file path
     * @param Product $product The product entity associated with icon
     * @param string|null $iconExtension The icon file extension (use for files without extension)
     *
     * @return void
     */
    public function updateProductIcon(string $iconPath, Product $product, ?string $iconExtension = null): void
    {
        // icon file data
        $fileName = basename($iconPath);

        // generate unique icon file name
        $fileName = $this->generateAssetName('icons', $fileName);

        // get icon file content
        $resourceContent = file_get_contents($iconPath);
        if ($resourceContent === false) {
            $this->errorManager->handleError(
                message: 'Error to update product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // apped icon extension if exists
        if ($iconExtension != null) {
            $fileName .= '.' . $iconExtension;
        }

        // get old icon file
        $oldIcon = $product->getIcon();
        if ($oldIcon === null) {
            $this->errorManager->handleError(
                message: 'Product: ' . $product->getName() . ' does not have icon',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }
        $oldIconFile = $oldIcon->getIconFile();
        if ($oldIconFile === null) {
            $this->errorManager->handleError(
                message: 'Product: ' . $product->getName() . ' icon file not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get product icon id
        $productIcon = $product->getIcon();
        if ($productIcon === null) {
            $this->errorManager->handleError(
                message: 'Product: ' . $product->getName() . ' does not have icon',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }
        $productIconId = $productIcon->getId();
        if ($productIconId === null) {
            $this->errorManager->handleError(
                message: 'Product: ' . $product->getName() . ' does not have icon',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        /** @var ProductIcon $icon */
        $icon = $this->productIconRepository->find($productIconId);

        // update icon entity
        $icon->setIconFile($fileName);

        try {
            // save icon entity to database
            $this->entityManager->flush();

            // update icon file
            $this->storageUtil->createStorageResource('icons', $fileName, $resourceContent);
            $this->storageUtil->deleteStorageResource('icons', $oldIconFile);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to update product icon',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // invalidate cache data
        $this->cacheManager->invalidateAllKeysStartsWith('product_' . $product->getId() . '_currency_');
        $this->cacheManager->invalidateAllKeysStartsWith('product_list_search_');

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' icon updated: ' . $fileName,
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
     * Get product image id by file name
     *
     * @param string $fileName The product image file name
     *
     * @return int|null The product image id or null if product image not found
     */
    public function getProductImageIdByFileName(string $fileName): ?int
    {
        $image = $this->productImageRepository->findOneBy(['image_file' => $fileName]);
        if ($image != null) {
            return $image->getId();
        }
        return null;
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
     * Check if product have image
     *
     * @param Product $product The product entity
     * @param int $imageId The product image id
     *
     * @return bool True if product have image, false otherwise
     */
    public function checkIfProductHaveImage(Product $product, int $imageId): bool
    {
        // get product image by id
        $image = $this->getProductImageById($imageId);

        // check if product image exists
        if ($image == null) {
            return false;
        }

        // get image file
        $imageFile = $image->getImageFile();

        return in_array($imageFile, $product->getImagesRaw());
    }

    /**
     * Get product image
     *
     * @param string $imageFile The product image file name
     *
     * @return string The product image content
     */
    public function getProductImage(string $imageFile): string
    {
        $image = $this->storageUtil->getStorageResource('images', $imageFile);

        // check if image exists
        if ($image === null) {
            $this->errorManager->handleError(
                message: 'Product image not found: ' . $imageFile,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $image;
    }

    /**
     * Create product image
     *
     * @param string $imagePath The product image file path
     * @param Product $product The product entity associated with image
     * @param string|null $imageExtension The image file extension (use for files without extension)
     *
     * @return void
     */
    public function createProductImage(string $imagePath, Product $product, ?string $imageExtension = null): void
    {
        // image file data
        $fileName = basename($imagePath);

        // generate unique image file name
        $fileName = $this->generateAssetName('images', $fileName);

        // get image file content
        $resourceContent = file_get_contents($imagePath);
        if ($resourceContent === false) {
            $this->errorManager->handleError(
                message: 'Error to create product image',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // apped image extension if exists
        if ($imageExtension != null) {
            $fileName .= '.' . $imageExtension;
        }

        // create image entity
        $image = new ProductImage();
        $image->setImageFile($fileName);
        $image->setProduct($product);

        try {
            // save image entity to database
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            // create image file
            $this->storageUtil->createStorageResource('images', $fileName, $resourceContent);
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create product image',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // invalidate cache data
        $this->cacheManager->invalidateAllKeysStartsWith('product_' . $product->getId() . '_currency_');
        $this->cacheManager->invalidateAllKeysStartsWith('product_list_search_');

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

        // invalidate cache data
        $this->cacheManager->invalidateAllKeysStartsWith('product_');

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product image deleted: ' . $image->getImageFile(),
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Generate unique asset name
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
