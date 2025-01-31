<?php

namespace App\Controller\Product;

use Exception;
use App\Util\AppUtil;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use App\Manager\CacheManager;
use OpenApi\Attributes\Items;
use App\Manager\ProductManager;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use App\Manager\CategoryManager;
use App\Manager\AttributeManager;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductStatsController
 *
 * Controller for getting product stats
 *
 * @package App\Controller\Product
 */
class ProductStatsController extends AbstractController
{
    private AppUtil $appUtil;
    private ErrorManager $errorManager;
    private CacheManager $cacheManager;
    private ProductManager $productManager;
    private CategoryManager $categoryManager;
    private AttributeManager $attributeManager;

    public function __construct(
        AppUtil $appUtil,
        ErrorManager $errorManager,
        CacheManager $cacheManager,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        AttributeManager $attributeManager
    ) {
        $this->appUtil = $appUtil;
        $this->errorManager = $errorManager;
        $this->cacheManager = $cacheManager;
        $this->productManager = $productManager;
        $this->categoryManager = $categoryManager;
        $this->attributeManager = $attributeManager;
    }

    /**
     * Get product stats
     *
     * @return JsonResponse Return product stats in json
     */
    #[Tag(name: "Product")]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The product stats',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new Property(property: 'total', type: 'integer', example: 1001),
                        new Property(property: 'active', type: 'integer', example: 1000),
                        new Property(property: 'inactive', type: 'integer', example: 1)
                    ]
                )
            ]
        )
    )]
    #[Route('/api/product/stats', methods:['GET'], name: 'get_product_stats')]
    public function getProductStats(): JsonResponse
    {
        // get product stats
        try {
            // get product stats from cache
            $cachedProductStats = $this->cacheManager->getCacheValue('product_stats');

            // check if product stats is cached and return it
            if ($cachedProductStats !== null) {
                $data = unserialize($cachedProductStats);
                return $this->json([
                    'status' => 'success',
                    'data' => $data,
                ], JsonResponse::HTTP_OK);
            }

            // get product stats from database
            $data = $this->productManager->getProductStats();

            // cache product stats
            $productCacheTTL = (int) $this->appUtil->getEnvValue('PRODUCT_CACHE_TTL');
            $this->cacheManager->saveCacheValue('product_stats', serialize($data), $productCacheTTL);

            // return product stats
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product stats get failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Get product categories list
     *
     * @return JsonResponse Return product categories list in json
     */
    #[Tag(name: "Product")]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The product categories list',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(property: 'data', type: 'array', items: new Items(type: 'string'), example: [
                    "Automotive Accessories",
                    "Beauty Products",
                    "Books & Stationery",
                    "Clothing",
                    "Electronics",
                    "Food & Beverages",
                    "Furniture",
                    "Home Appliances",
                    "New Category w2Rm2zKcgtrag5a5",
                    "New Category: vEDJw8",
                    "Non assigned test category",
                    "Sports Equipment",
                    "Toys"
                ])
            ]
        )
    )]
    #[Route('/api/product/categories', methods:['GET'], name: 'get_product_categories')]
    public function getProductCategories(): JsonResponse
    {
        // get product stats
        try {
            // get product categories list from cache
            $cachedProductCategories = $this->cacheManager->getCacheValue('product_categories');

            // check if product categories list is cached and return it
            if ($cachedProductCategories !== null) {
                $data = unserialize($cachedProductCategories);
                return $this->json([
                    'status' => 'success',
                    'data' => $data,
                ], JsonResponse::HTTP_OK);
            }

            // get product categories list from database
            $data = $this->categoryManager->getCategoriesList();

            // cache product categories list
            $productCacheTTL = (int) $this->appUtil->getEnvValue('PRODUCT_CACHE_TTL');
            $this->cacheManager->saveCacheValue('product_categories', serialize($data), $productCacheTTL);

            // return product categories list
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product categories list get failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Get product attributes list
     *
     * @return JsonResponse Return product attributes list in json
     */
    #[Tag(name: "Product")]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: 'The product attributes list',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: 'status', type: 'string', example: 'success'),
                new Property(property: 'data', type: 'array', items: new Items(type: 'string'), example: [
                    "Brand",
                    "Capacity",
                    "Color",
                    "Material",
                    "Non assigned test attribute",
                    "Power",
                    "Size",
                    "Weight"
                ])
            ]
        )
    )]
    #[Route('/api/product/attributes', methods:['GET'], name: 'get_product_attributes')]
    public function getProductAttributes(): JsonResponse
    {
        // get product stats
        try {
            // get product attributes list from cache
            $cachedProductAttributes = $this->cacheManager->getCacheValue('product_attributes');

            // check if product attributes list is cached and return it
            if ($cachedProductAttributes !== null) {
                $data = unserialize($cachedProductAttributes);
                return $this->json([
                    'status' => 'success',
                    'data' => $data,
                ], JsonResponse::HTTP_OK);
            }

            // get product attributes list from database
            $data = $this->attributeManager->getAttributesList();

            // cache product attributes list
            $productCacheTTL = (int) $this->appUtil->getEnvValue('PRODUCT_CACHE_TTL');
            $this->cacheManager->saveCacheValue('product_attributes', serialize($data), $productCacheTTL);

            // return product attributes list
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product attributes list get failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }
}
