<?php

namespace App\Controller\Admin\Product;

use Exception;
use OpenApi\Attributes as OA;
use App\Manager\ErrorManager;
use App\Manager\ProductManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductDeleteController
 *
 * Controller for deleting product from database
 *
 * @package App\Controller\Admin\Product
 */
class ProductDeleteController extends AbstractController
{
    private ErrorManager $errorManager;
    private ProductManager $productManager;

    public function __construct(ErrorManager $errorManager, ProductManager $productManager)
    {
        $this->errorManager = $errorManager;
        $this->productManager = $productManager;
    }

    /**
     * Delete product data
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The delete status is json response
     */
    #[OA\Delete(
        summary: 'Delete product action',
        description: 'Delete product and return status',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product-id', type: 'int', description: 'Product id to delete', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success product data delete message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Product data deleted successfully!")
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Invalid request data")
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_NOT_FOUND,
                description: 'Product not found message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Product not found")
                    ]
                )
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/delete', methods:['DELETE'], name: 'delete_product')]
    public function deleteProduct(Request $request): JsonResponse
    {
        // get data from request
        $data = json_decode($request->getContent(), true);

        // check if json input is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid JSON payload.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // get request parameters
        $productId = (int) trim($data['product-id'] ?? '');

        // check if product id is valid
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id is not set or invalid.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var \App\Entity\Product $product */
        $product = $this->productManager->getProductById($productId);

        // check if product exists
        if ($product == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id: ' . $productId . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // delete product data
        try {
            $this->productManager->deleteProduct($productId);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product delete error',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'Product data deleted successfully!'
        ], JsonResponse::HTTP_OK);
    }
}
