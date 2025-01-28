<?php

namespace App\Controller\Product;

use Exception;
use App\Util\AppUtil;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use App\Manager\CacheManager;
use OpenApi\Attributes as OA;
use App\Manager\ProductManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Parameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductGetController
 *
 * Controller for getting products data
 *
 * @package App\Controller\Product
 */
class ProductGetController extends AbstractController
{
    private AppUtil $appUtil;
    private ErrorManager $errorManager;
    private CacheManager $cacheManager;
    private ProductManager $productManager;

    public function __construct(
        AppUtil $appUtil,
        ErrorManager $errorManager,
        CacheManager $cacheManager,
        ProductManager $productManager
    ) {
        $this->appUtil = $appUtil;
        $this->errorManager = $errorManager;
        $this->cacheManager = $cacheManager;
        $this->productManager = $productManager;
    }

    /**
     * Get product by id
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Return product data as json response
     */
    #[Tag(name: "Product")]
    #[Parameter(name: 'id', in: 'query', description: 'Product id', example: '1', required: true)]
    #[Parameter(name: 'currency', in: 'query', description: 'Product price currency', example: 'USD', required: false)]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The product data')]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Product not found')]
    #[Route('/api/product/get', methods:['GET'], name: 'get_product')]
    public function getProductById(Request $request): JsonResponse
    {
        // get request parameters
        $productId = $request->get('id');
        $requestedCurrency = $request->get('currency', null);

        // check if product parameter id is set
        if (!$productId) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id parameter is required!',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // set requested currency to uppercase
        if ($requestedCurrency !== null) {
            $requestedCurrency = strtoupper($requestedCurrency);
        }

        // init cache key for product
        $cacheKey = 'product_' . $productId . '_currency_' . $requestedCurrency;

        // get product data from cache storage
        $cachedProductData = $this->cacheManager->getCacheValue($cacheKey);

        // check if product data is cached and return it
        if ($cachedProductData !== null) {
            return $this->json([
                'statusf' => 'success',
                'data' => unserialize($cachedProductData),
            ], JsonResponse::HTTP_OK);
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id: ' . $productId . ' not found.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // format product data
        $data = $this->productManager->formatProductData($product, $requestedCurrency);

        // save product data to cache storage
        $productCacheTTL = (int) $this->appUtil->getEnvValue('PRODUCT_CACHE_TTL');
        $this->cacheManager->saveCacheValue($cacheKey, serialize($data), $productCacheTTL);

        // return product data
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get products list by filter
     *
     * @param Request $request Request object
     *
     * @return JsonResponse Return products list as json response
     */
    #[OA\Post(
        summary: 'Get a list of products with filters',
        description: 'Retrieve a filtered list of products based on provided criteria',
        tags: ['Product'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'search', type: 'string', description: 'Search text', example: 'Coffee maker'),
                    new OA\Property(
                        property: 'attributes',
                        type: 'array',
                        description: 'Filter products by attributes (array of attribute objects)',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'name', type: 'string', description: 'Attribute name', example: 'color'),
                                new OA\Property(property: 'value', type: 'string', description: 'Attribute value', example: 'red'),
                            ]
                        ),
                        example: [
                            'Power' => '1000W'
                        ]
                    ),
                    new OA\Property(
                        property: 'categories',
                        type: 'array',
                        description: 'Filter products by categories (array of category names)',
                        items: new OA\Items(type: 'integer'),
                        example: [
                            'Electronics'
                        ]
                    ),
                    new OA\Property(property: 'page', type: 'int', description: 'Page number', example: 1),
                    new OA\Property(property: 'limit', type: 'int', description: 'Number of products per page', example: 100),
                    new OA\Property(property: 'sort', type: 'string', description: 'Sort by field', example: 'name'),
                    new OA\Property(property: 'currency', type: 'string', description: 'Product price currency', example: 'USD')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The list of filtered products'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message'
            ),
            new OA\Response(
                response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                description: 'Server error'
            ),
        ]
    )]
    #[Route('/api/product/list', methods:['POST'], name: 'get_product_list')]
    public function getProductListByFilter(Request $request): JsonResponse
    {
        // decode json request body
        $content = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid JSON payload.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // extract request parameters
        $search = $content['search'] ?? null;
        $attributes = $content['attributes'] ?? [];
        $categories = $content['categories'] ?? [];
        $page = $content['page'] ?? 1;
        $limit = $content['limit'] ?? $this->appUtil->getEnvValue('LIMIT_CONTENT_PER_PAGE');
        $sort = $content['sort'] ?? null;
        $currency = $content['currency'] ?? null;

        // get ttl for product cache
        $productCacheTTL = (int) $this->appUtil->getEnvValue('PRODUCT_CACHE_TTL');

        // cache key for list filter
        $cacheKey = 'product_list_search_' . $search . '_attributes_' . implode('_', $attributes) . '_categories_' . implode('_', $categories) . '_page_' . $page . '_limit_' . $limit . '_sort_' . $sort . '_currency_' . $currency;
        $statsCacheKey = "product_stats";

        // get data from cache
        $cachedStats = $this->cacheManager->getCacheValue($statsCacheKey);
        $cachedProductListData = $this->cacheManager->getCacheValue($cacheKey);

        // check if stats data is cached
        if ($cachedStats !== null) {
            $stats = unserialize($cachedStats);
        } else {
            $stats = $this->productManager->getProductStats();
            $this->cacheManager->saveCacheValue($statsCacheKey, serialize($stats), $productCacheTTL);
        }

        // check if product list data is cached and return it
        if ($cachedProductListData !== null) {
            $cachedData = unserialize($cachedProductListData);
            return $this->json([
                'status' => 'success',
                'products_data' => $cachedData['products'],
                'pagination_info' => $cachedData['pagination_info'],
                'stats' => $stats,
            ], JsonResponse::HTTP_OK);
        }

        // get filtered product list
        try {
            $data = $this->productManager->getProductsList(
                search: $search,
                attributeValues: $attributes,
                categories: $categories,
                page: $page,
                limit: $limit,
                sort: $sort,
                currency: $currency,
            );

            // save product list data to cache
            $cacheData = [
                'products' => $data['products'],
                'pagination_info' => $data['pagination_info'],
                'stats' => $stats,
            ];
            $this->cacheManager->saveCacheValue($cacheKey, serialize($cacheData), $productCacheTTL);

            return $this->json([
                'status' => 'success',
                'products_data' => $data['products'],
                'pagination_info' => $data['pagination_info'],
                'stats' => $stats,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product list get failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
