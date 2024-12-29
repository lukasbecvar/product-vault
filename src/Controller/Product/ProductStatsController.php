<?php

namespace App\Controller\Product;

use OpenApi\Attributes\Tag;
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
    private ProductManager $productManager;
    private CategoryManager $categoryManager;
    private AttributeManager $attributeManager;

    public function __construct(
        ProductManager $productManager,
        CategoryManager $categoryManager,
        AttributeManager $attributeManager
    ) {
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
        $data = $this->productManager->getProductStats();

        // return product stats
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
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
        $data = $this->categoryManager->getCategoriesList();

        // return product stats
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
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
        $data = $this->attributeManager->getAttributesList();

        // return product stats
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }
}
