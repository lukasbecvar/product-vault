<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Util\AppUtil;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Attribute;
use App\Entity\ProductCategory;
use App\Entity\ProductAttribute;
use App\Util\CurrencyConvertorUtil;
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
    private AppUtil $appUtil;
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;
    private CurrencyConvertorUtil $currencyConvertorUtil;

    public function __construct(
        AppUtil $appUtil,
        LogManager $logManager,
        ErrorManager $errorManager,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        CurrencyConvertorUtil $currencyConvertorUtil
    ) {
        $this->appUtil = $appUtil;
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->currencyConvertorUtil = $currencyConvertorUtil;
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
     * Format product data
     *
     * @param Product $product The product entity
     * @param string|null $requestedCurrency The product price currency for currency coversion (default: EUR)
     *
     * @return array<mixed> The formatted product data
     */
    public function formatProductData(Product $product, ?string $requestedCurrency = null): array
    {
        // get product data
        $id = $product->getId();
        $name = $product->getName();
        $description = $product->getDescription();
        $price = (float) $product->getPrice();
        $priceCurrency = $product->getPriceCurrency();
        $active = $product->isActive();
        $categories = $product->getCategoriesRaw();
        $attributes = $product->getProductAttributesRaw();
        $icon = $product->getIcon();
        $images = $product->getImagesRaw();

        // check if price currency is valid
        if ($priceCurrency === null) {
            $this->errorManager->handleError(
                message: 'Product price currency is not set',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // convert price from default currency to required currency
        if ($requestedCurrency !== null && $priceCurrency !== strtoupper($requestedCurrency)) {
            $price = $this->currencyConvertorUtil->convertCurrency($priceCurrency, $price, $requestedCurrency);
            $priceCurrency = strtoupper($requestedCurrency);
        }

        // return formatted product data
        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'priceCurrency' => $priceCurrency,
            'active' => $active,
            'categories' => $categories,
            'attributes' => $attributes,
            'icon' => $icon,
            'images' => $images,
        ];
    }

    /**
     * Get products list (wrapper for product repository)
     *
     * @param string|null $search Search text for name or description (optional)
     * @param array<string, string>|null $attributeValues Attribute name-value pairs (optional)
     * @param array<string>|null $categories List of category names (optional)
     * @param int $page Page number for pagination (1-based)
     * @param int $limit Number of products per page (default: 100)
     * @param string|null $sort Sort by field: 'name', 'price', or 'added_time' (default: null)
     * @param string|null $currency The product price currency for currency coversion (default: EUR)
     *
     * @return array{
     *     products: array<mixed>,
     *     pagination_info: array{
     *         total_pages: int,
     *         current_page_number: int,
     *         total_items: int,
     *         items_per_actual_page: int,
     *         last_page_number: int,
     *         is_next_page_exists: bool,
     *         is_previous_page_exists: bool
     *     }
     * }
     */
    public function getProductsList(
        ?string $search = null,
        ?array $attributeValues = null,
        ?array $categories = null,
        int $page = 1,
        int $limit = 0,
        ?string $sort = null,
        ?string $currency = null
    ) {
        // set default limit
        if ($limit === 0) {
            $limit = (int) $this->appUtil->getEnvValue('LIMIT_CONTENT_PER_PAGE');
        }

        // get products list
        $products = $this->productRepository->findByFilterCriteria($search, $attributeValues, $categories, $page, $limit, $sort);

        // format products data
        $products_data = [];
        foreach ($products as $product) {
            $products_data[] = $this->formatProductData($product, $currency);
        }

        // get pagination info
        $paginationInfo = $this->productRepository->getPaginationInfo($search, $attributeValues, $categories, $page, $limit);

        // return products list and pagination info
        return [
            'products' => $products_data,
            'pagination_info' => $paginationInfo,
        ];
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

        // delete product
        try {
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
     * Assign category to product
     *
     * @param Product $product The product entity
     * @param Category $category The category entity
     *
     * @return void
     */
    public function assignCategoryToProduct(Product $product, Category $category): void
    {
        // check if product already has category
        if (in_array($category->getName(), $product->getCategoriesRaw())) {
            $this->errorManager->handleError(
                message: 'Product: ' . $product->getName() . ' already has category',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // assign category to product
        $productCategory = new ProductCategory();
        $productCategory->setProduct($product);
        $productCategory->setCategory($category);

        try {
            // save product entity to database
            $this->entityManager->persist($productCategory);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to assign category to product',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' assigned to category: ' . $category->getName(),
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

    /**
     * Assign attribute to product
     *
     * @param Product $product The product entity
     * @param Attribute $attribute The attribute entity
     * @param mixed $value The attribute value
     *
     * @return void
     */
    public function assignAttributeToProduct(Product $product, Attribute $attribute, mixed $value): void
    {
        // check if product already has attribute
        if (in_array($attribute->getName(), $product->getProductAttributesList())) {
            $this->updateAttributeValue($product, $attribute, $value);
            return;
        }

        // get value type
        $valueType = gettype($value);

        // assign attribute to product
        $productAttribute = new ProductAttribute();
        $productAttribute->setProduct($product);
        $productAttribute->setType($valueType);
        $productAttribute->setAttribute($attribute);
        $productAttribute->setValue($value);

        try {
            // save product attribute to database
            $this->entityManager->persist($productAttribute);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to assign attribute to product',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' assigned to attribute: ' . $attribute->getName(),
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Update attribute value
     *
     * @param Product $product The product entity
     * @param Attribute $attribute The attribute entity
     * @param mixed $value The attribute value
     *
     * @return void
     */
    public function updateAttributeValue(Product $product, Attribute $attribute, mixed $value): void
    {
        // get attribute by id
        $productAttribute = $this->entityManager->getRepository(ProductAttribute::class)->findOneBy([
            'product' => $product,
            'attribute' => $attribute,
        ]);

        // check if attribute exists
        if ($productAttribute == null) {
            $this->errorManager->handleError(
                message: 'Attribute id: ' . $attribute->getId() . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // update attribute value
        $productAttribute->setValue($value);

        try {
            // save product attribute to database
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to update attribute value',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' attribute value updated',
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Remove attribute from product
     *
     * @param Product $product The product entity
     * @param Attribute $attribute The attribute entity
     *
     * @return void
     */
    public function removeAttributeFromProduct(Product $product, Attribute $attribute): void
    {
        // get product attribute by id
        $productAttribute = $this->entityManager->getRepository(ProductAttribute::class)->findOneBy([
            'product' => $product,
            'attribute' => $attribute,
        ]);

        // check if attribute exists
        if ($productAttribute == null) {
            $this->errorManager->handleError(
                message: 'Attribute id: ' . $attribute->getId() . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        try {
            $this->entityManager->remove($productAttribute);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to remove attribute from product',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Product: ' . $product->getName() . ' removed from attribute: ' . $attribute->getName(),
            level: LogManager::LEVEL_INFO,
        );
    }
}
