<?php

namespace App\Controller\Product;

use OpenApi\Attributes\Tag;
use App\Manager\ProductManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Parameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class GetProductController
 *
 * Controller for getting products data
 *
 * @package App\Controller\Product
 */
class GetProductController extends AbstractController
{
    private ProductManager $productManager;

    public function __construct(ProductManager $productManager)
    {
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

        // check if product id is set
        if (!$productId) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id parameter is required',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get product by id
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id: ' . $productId . ' not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // format product data
        $product = $this->productManager->formatProductData($product, $requestedCurrency);

        return $this->json([
            'status' => 'success',
            'product' => $product,
        ], JsonResponse::HTTP_OK);
    }
}
