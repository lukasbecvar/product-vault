<?php

namespace App\Controller\Product;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use App\Manager\ProductManager;
use OpenApi\Attributes\Response;
use App\Manager\CategoryManager;
use App\Manager\AttributeManager;
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
    private ErrorManager $errorManager;
    private ProductManager $productManager;
    private CategoryManager $categoryManager;
    private AttributeManager $attributeManager;

    public function __construct(
        ErrorManager $errorManager,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        AttributeManager $attributeManager
    ) {
        $this->errorManager = $errorManager;
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
    #[Response(response: JsonResponse::HTTP_OK, description: 'The product stats')]
    #[Route('/api/product/stats', methods:['GET'], name: 'get_product_stats')]
    public function getProductStats(): JsonResponse
    {
        // get product stats
        try {
            $data = $this->productManager->getProductStats();
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product stats get failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Get product categories list
     *
     * @return JsonResponse Return product categories list in json
     */
    #[Tag(name: "Product")]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The product categories list')]
    #[Route('/api/product/categories', methods:['GET'], name: 'get_product_categories')]
    public function getProductCategories(): JsonResponse
    {
        // get product stats
        try {
            $data = $this->categoryManager->getCategoriesList();
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product categories list get failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Get product attributes list
     *
     * @return JsonResponse Return product attributes list in json
     */
    #[Tag(name: "Product")]
    #[Response(response: JsonResponse::HTTP_OK, description: 'The product attributes list')]
    #[Route('/api/product/attributes', methods:['GET'], name: 'get_product_attributes')]
    public function getProductAttributes(): JsonResponse
    {
        // get product stats
        try {
            $data = $this->attributeManager->getAttributesList();
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product attributes list get failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
