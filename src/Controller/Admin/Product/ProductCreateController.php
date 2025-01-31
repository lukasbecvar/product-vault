<?php

namespace App\Controller\Admin\Product;

use Exception;
use App\Util\AppUtil;
use App\DTO\ProductDTO;
use App\Manager\ErrorManager;
use OpenApi\Attributes as OA;
use App\Manager\ProductManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductCreateController
 *
 * API controller for creating a new product
 *
 * @package App\Controller\Admin\Product
 */
class ProductCreateController extends AbstractController
{
    private AppUtil $appUtil;
    private ErrorManager $errorManager;
    private ValidatorInterface $validator;
    private ProductManager $productManager;

    public function __construct(
        AppUtil $appUtil,
        ErrorManager $errorManager,
        ValidatorInterface $validator,
        ProductManager $productManager
    ) {
        $this->appUtil = $appUtil;
        $this->validator = $validator;
        $this->errorManager = $errorManager;
        $this->productManager = $productManager;
    }

    /**
     * Create product endpoint
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[OA\Post(
        summary: 'Create product action',
        description: 'Create a new product in database',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: 'Product name', example: 'Testing product'),
                    new OA\Property(property: 'description', type: 'string', description: 'Product description', example: 'Testing product description'),
                    new OA\Property(property: 'price', type: 'int', description: 'Product price', example: 100),
                    new OA\Property(property: 'price-currency', type: 'string', description: 'Product price currency', example: 'USD'),
                    new OA\Property(
                        property: 'categories',
                        type: 'array',
                        description: 'List of categories for the product',
                        minItems: 1,
                        example: ["Electronics", "Home Appliances"],
                        items: new OA\Items(
                            type: 'string'
                        )
                    ),
                    new OA\Property(
                        property: 'attributes',
                        type: 'array',
                        description: 'List of product attributes',
                        example: [
                            ["name" => "Color", "attribute-value" => "Red"],
                            ["name" => "Size", "attribute-value" => "XXL"]
                        ],
                        items: new OA\Items(
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', description: 'Attribute name', example: 'Color'),
                                    new OA\Property(property: 'attributeValue', type: 'string', description: 'Attribute value', example: 'Red')
                                ]
                            )
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_CREATED,
                description: 'The success product create message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Product created successfully!"),
                        new OA\Property(property: "product_data", type: "array", items: new OA\Items(type: "object"))
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
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/create', methods:['POST'], name: 'create_product')]
    public function createProduct(Request $request): JsonResponse
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

        // set data to DTO object
        $productDTO = new ProductDTO();
        $productDTO->name = trim($data['name'] ?? '');
        $productDTO->description = trim($data['description'] ?? '');
        $productDTO->price = trim($data['price'] ?? '');
        $productDTO->priceCurrency = trim($data['price-currency'] ?? '');

        /** @var array<string> $categories */
        $categories = $data['categories'] ?? [];
        /** @var array<array<array<mixed>>> $attributes */
        $attributes = $data['attributes'] ?? [];

        // validate data using DTO properties
        $violations = $this->validator->validate($productDTO);

        // get validation errors
        $errors = [];
        foreach ($violations as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $errors[] = $violation->getMessage();
        }

        // return error response if any errors found
        if (count($errors) > 0) {
            return $this->json([
                'status' => 'error',
                'message' => implode(', ', $errors)
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if categories length is greater than 0
        if (count($data['categories']) == 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product requires minimal 1 category.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if attributes is valid
        if (!empty($attributes)) {
            if (!$this->appUtil->validateAttributes($attributes)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid attributes format.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        // create new product
        try {
            $data = $this->productManager->createProduct(
                $productDTO->name,
                $productDTO->description,
                $productDTO->price,
                $productDTO->priceCurrency,
                $categories,
                $attributes
            );
            return $this->json([
                'status' => 'success',
                'message' => 'Product: ' . $productDTO->name . ' created successfully!',
                'product_data' => $this->productManager->formatProductData($data)
            ], JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product create failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }
}
