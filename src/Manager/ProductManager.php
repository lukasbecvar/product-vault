<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductManager
 *
 * Manager for manipulating with products database
 *
 * @package App\Manager
 */
class ProductManager
{
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private CategoryManager $categoryManager;
    private AttributeManager $attributeManager;
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LogManager $logManager,
        ErrorManager $errorManager,
        CategoryManager $categoryManager,
        AttributeManager $attributeManager,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->categoryManager = $categoryManager;
        $this->attributeManager = $attributeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Get product by id
     *
     * @param int $productId The product id
     *
     * @return Product|null The product entity object
     */
    public function getProductById(int $productId): ?Product
    {
        return $this->productRepository->find($productId);
    }

    /**
     * Create new product
     *
     * @param string $name The product name
     * @param string $description The product description
     * @param string $price The product price
     * @param string $priceCurrency The product price currency (default: EUR)
     *
     * @return Product The created product entity object
     */
    public function createProduct(string $name, string $description, string $price, string $priceCurrency = 'EUR'): Product
    {
        // get current time
        $currentTime = new DateTime();

        // create new product entity
        $product = new Product();
        $product->setName($name);
        $product->setDescription($description);
        $product->setAddedTime($currentTime);
        $product->setLastEditTime($currentTime);
        $product->setPrice($price);
        $product->setPriceCurrency(strtoupper($priceCurrency));
        $product->setActive(true);

        // save product entity to database
        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product create error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' created',
            level: LogManager::LEVEL_INFO,
        );

        return $product;
    }

    /**
     * Edit product by id
     *
     * @param int $productId The product id to edit
     * @param string|null $name The new product name (if null, the product name will not be changed)
     * @param string|null $description The new product description (if null, the product description will not be changed)
     * @param string|null $price The new product price (if null, the product price will not be changed)
     * @param string|null $priceCurrency The new product price currency (if null, the product price currency will not be changed)
     *
     * @return void
     */
    public function editProduct(int $productId, ?string $name, ?string $description, ?string $price, ?string $priceCurrency): void
    {
        // get product by id
        $product = $this->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // update product data
        if ($name !== null) {
            $product->setName($name);
        }
        if ($description !== null) {
            $product->setDescription($description);
        }
        if ($price !== null) {
            $product->setPrice($price);
        }
        if ($priceCurrency !== null) {
            $product->setPriceCurrency(strtoupper($priceCurrency));
        }

        // save product entity to database
        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product edit error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' edited',
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Delete product from database
     *
     * @param int $productId The product id to delete
     *
     * @return void
     */
    public function deleteProduct(int $productId): void
    {
        // get product by id
        $product = $this->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        try {
            // delete related attributes
            $this->attributeManager->deleteAttributesByProductId($productId);

            // delete related categories
            $this->categoryManager->deleteCategoriesByProductId($productId);

            // delete product
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product delete error id: ' . $productId,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' with id: ' . $productId . ' deleted',
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Activate product by id
     *
     * @param int $productId The product id to activate
     *
     * @return void
     */
    public function activateProduct(int $productId): void
    {
        // get product by id
        $product = $this->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if product is already active
        if ($product->isActive()) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' is already active',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // update product data
        $product->setActive(true);

        // save product entity to database
        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product activate error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' activated',
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Deactivate product by id
     *
     * @param int $productId The product id to deactivate
     *
     * @return void
     */
    public function deactivateProduct(int $productId): void
    {
        // get product by id
        $product = $this->getProductById($productId);

        // check if product exists
        if ($product == null) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if product is already inactive
        if (!$product->isActive()) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $productId . ' is already inactive',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // update product data
        $product->setActive(false);

        // save product entity to database
        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Product deactivate error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' deactivated',
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Assing category to product
     *
     * @param Product $product The product entity
     * @param Category $category The category entity
     *
     * @return void
     */
    public function assingCategoryToProduct(Product $product, Category $category): void
    {
        // check if product already has category
        if (in_array($category->getName(), $product->getCategoriesRaw())) {
            $this->errorManager->handleError(
                message: 'Product id: ' . $product->getName() . ' already has category',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // assing category to product
        $productCategory = new ProductCategory();
        $productCategory->setProduct($product);
        $productCategory->setCategory($category);

        try {
            // save product entity to database
            $this->entityManager->persist($productCategory);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to assing category to product',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' assinged to category: ' . $category->getName(),
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Remove category from product
     *
     * @param Product $product The product entity
     * @param Category $category The category entity
     *
     * @return void
     */
    public function removeCategoryFromProduct(Product $product, Category $category): void
    {
        // get category by id
        $productCategory = $this->entityManager->getRepository(ProductCategory::class)->findOneBy([
            'product' => $product,
            'category' => $category,
        ]);

        // check if category exists
        if ($productCategory == null) {
            $this->errorManager->handleError(
                message: 'Category id: ' . $category->getId() . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        try {
            $this->entityManager->remove($productCategory);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to remove category from product',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' removed from category: ' . $category->getName(),
            level: LogManager::LEVEL_INFO,
        );
    }
}