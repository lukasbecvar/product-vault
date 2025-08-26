<?php

namespace App\Controller\Admin\Product;

use Exception;
use App\DTO\ProductDTO;
use OpenApi\Attributes as OA;
use App\Manager\ErrorManager;
use App\Manager\ProductManager;
use App\Manager\CategoryManager;
use App\Manager\AttributeManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductEditController
 *
 * Controller for editing product data
 *
 * @package App\Controller\Admin\Product
 */
class ProductEditController extends AbstractController
{
    private ErrorManager $errorManager;
    private ValidatorInterface $validator;
    private ProductManager $productManager;
    private CategoryManager $categoryManager;
    private AttributeManager $attributeManager;

    public function __construct(
        ErrorManager $errorManager,
        ValidatorInterface $validator,
        ProductManager $productManager,
        CategoryManager $categoryManager,
        AttributeManager $attributeManager
    ) {
        $this->validator = $validator;
        $this->errorManager = $errorManager;
        $this->productManager = $productManager;
        $this->categoryManager = $categoryManager;
        $this->attributeManager = $attributeManager;
    }

    /**
     * Update product data
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status is json response
     */
    #[OA\Patch(
        summary: 'Update product data action',
        description: 'Update product data and return status',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product-id', type: 'int', description: 'Product id to edit', example: 1),
                    new OA\Property(property: 'name', type: 'string', description: 'Product name', example: 'Testing product'),
                    new OA\Property(property: 'description', type: 'string', description: 'Product description', example: 'Testing product description'),
                    new OA\Property(property: 'price', type: 'int', description: 'Product price', example: 100),
                    new OA\Property(property: 'price-currency', type: 'string', description: 'Product price currency', example: 'USD')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success product data update message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Product data updated successfully!"),
                        new OA\Property(property: "product", type: "array", items: new OA\Items(type: "object"))
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
                        new OA\Property(property: "message", type: "string", example: "Invalid request data!")
                    ]
                )
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/update', methods:['PATCH'], name: 'update_product_data')]
    public function updateProductData(Request $request): JsonResponse
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
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = trim($data['price'] ?? '');
        $priceCurrency = trim($data['price-currency'] ?? '');

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

        // get product data from database if not set new data
        if ($name == null) {
            $name = $product->getName();
        }
        if ($description == null) {
            $description = $product->getDescription();
        }
        if ($price == null) {
            $price = $product->getPrice();
        }
        if ($priceCurrency == null) {
            $priceCurrency = $product->getPriceCurrency();
        }

        // check if product data is set
        if ($name == null || $description == null || $price == null || $priceCurrency == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error to get product data from database.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // set new data to product validation dto object
        $productDTO = new ProductDTO();
        $productDTO->name = $name;
        $productDTO->description = $description;
        $productDTO->price = $price;
        $productDTO->priceCurrency = $priceCurrency;

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

        // update product data
        try {
            $this->productManager->editProduct(
                $productId,
                $productDTO->name,
                $productDTO->description,
                $productDTO->price,
                $productDTO->priceCurrency
            );
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product edit error',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        /** @var \App\Entity\Product $product */
        $product = $this->productManager->getProductById($productId);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'Product data updated successfully!',
            'product_data' => $this->productManager->formatProductData($product)
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update product activity
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status is json response
     */
    #[OA\Patch(
        summary: 'Update product activity action',
        description: 'Update product activity and return status',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product-id', type: 'int', description: 'Product id to edit', example: 1),
                    new OA\Property(property: 'active', type: 'string', description: 'Product active status', example: 'true')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success product data update message',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Product data updated successfully!"),
                        new OA\Property(property: "product", type: "object", example: [])
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
                        new OA\Property(property: "message", type: "string", example: "Invalid request data!")
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
                        new OA\Property(property: "message", type: "string", example: "Product not found!")
                    ]
                )
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/update/activity', methods:['PATCH'], name: 'update_product_activity')]
    public function updateProductActivity(Request $request): JsonResponse
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
        $active = trim($data['active'] ?? '');

        // check if product id is valid
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id is not set or invalid.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if active is valid
        if ($active == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Active status not set.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!in_array($active, ['true', 'false'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Active status is not valid (allowed: true, false).'
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

        // update product data
        try {
            if ($active === 'true') {
                $this->productManager->activateProduct($productId);
            } else {
                $this->productManager->deactivateProduct($productId);
            }
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product activate error',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        /** @var \App\Entity\Product $product */
        $product = $this->productManager->getProductById($productId);

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'Product data updated successfully!',
            'product_data' => $this->productManager->formatProductData($product)
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update product categories
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status is json response
     */
    #[OA\Patch(
        summary: 'Update product category action',
        description: 'Update product category and return status',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product-id', type: 'int', description: 'Product id to edit', example: 1),
                    new OA\Property(property: 'process', type: 'string', description: 'Process (add, remove)', example: 'add'),
                    new OA\Property(
                        property: 'category-list',
                        type: 'array',
                        description: 'List of categories for the product',
                        minItems: 1,
                        example: ["Electronics", "Home Appliances"],
                        items: new OA\Items(
                            type: 'string'
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success product data update message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product data updated successfully!')
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid request data!')
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_NOT_FOUND,
                description: 'Product not found message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product not found!')
                    ]
                )
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/update/categories', methods:['PATCH'], name: 'update_product_categories')]
    public function updateProductCategories(Request $request): JsonResponse
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
        $process = trim($data['process'] ?? '');
        $categoryList = $data['category-list'] ?? [];

        // check if product id is valid
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id is not set or invalid.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if process is valid
        if ($process == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Process not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!in_array($process, ['add', 'remove'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Process is not valid (allowed: add, remove).'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if category list is valid
        if ($categoryList == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category list not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!is_array($categoryList)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category list is not valid.'
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

        // update product data
        try {
            if ($process === 'add') {
                foreach ($categoryList as $categoryName) {
                    $category = $this->categoryManager->getCategoryByName($categoryName);

                    // check if category exist
                    if ($category == null) {
                        $this->categoryManager->createCategory($categoryName);
                        $category = $this->categoryManager->getCategoryByName($categoryName);
                    }

                    // check if category not exist
                    if ($category == null) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'Category: ' . $categoryName . ' not found.'
                        ], JsonResponse::HTTP_NOT_FOUND);
                    }
                    $this->productManager->assignCategoryToProduct($product, $category);
                }
            } else {
                foreach ($categoryList as $categoryName) {
                    $category = $this->categoryManager->getCategoryByName($categoryName);
                    if ($category == null) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'Category: ' . $categoryName . ' not found.'
                        ], JsonResponse::HTTP_NOT_FOUND);
                    }
                    $this->productManager->removeCategoryFromProduct($product, $category);
                }
            }
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product update error',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'Product data updated successfully!'
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update product attributes
     *
     * @param Request $request The request object
     *
     * @return JsonResponse The update status is json response
     */
    #[OA\Patch(
        summary: 'Update product attribute action',
        description: 'Update product attribute and return status',
        tags: ['Admin (product manager)'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product-id', type: 'int', description: 'Product id to edit', example: 1),
                    new OA\Property(property: 'process', type: 'string', description: 'Process (add (update), remove)', example: 'add'),
                    new OA\Property(property: 'attribute-name', type: 'string', description: 'Attribute name', example: 'Color'),
                    new OA\Property(property: 'attribute-value', type: 'string', description: 'Attribute value', example: 'red')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: JsonResponse::HTTP_OK,
                description: 'The success product data update message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product data updated successfully!')
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_BAD_REQUEST,
                description: 'Invalid request data message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid request data!')
                    ]
                )
            ),
            new OA\Response(
                response: JsonResponse::HTTP_NOT_FOUND,
                description: 'Product not found message',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product not found!')
                    ]
                )
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/update/attribute', methods:['PATCH'], name: 'update_product_attribute')]
    public function updateProductAttribute(Request $request): JsonResponse
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
        $process = trim($data['process'] ?? '');
        $attributeName = trim($data['attribute-name'] ?? '');
        $attributeValue = trim($data['attribute-value'] ?? '');

        // check if product id is valid
        if ($productId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Product id is not set or invalid.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if process is valid
        if ($process == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Process not set'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!in_array($process, ['add', 'remove'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Process is not valid (allowed: add, remove).'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if attribute name is valid
        if ($attributeName == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Attribute name not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if attribute value is valid
        if ($attributeValue == null && $process !== 'remove') {
            return $this->json([
                'status' => 'error',
                'message' => 'Attribute value not set.'
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

        // get attribute by name
        $attribute = $this->attributeManager->getAttributeByName($attributeName);

        // check if attribute not exist
        if ($attribute == null) {
            $this->attributeManager->createAttribute($attributeName);
            $attribute = $this->attributeManager->getAttributeByName($attributeName);
        }

        // check if attribute exist
        if ($attribute == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Attribute: ' . $attributeName . ' not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // update product data
        try {
            if ($process === 'add') {
                $this->productManager->assignAttributeToProduct($product, $attribute, $attributeValue);
            } else {
                $this->productManager->removeAttributeFromProduct($product, $attribute);
            }
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Product update error',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        // return success response
        return $this->json([
            'status' => 'success',
            'message' => 'Product data updated successfully!'
        ], JsonResponse::HTTP_OK);
    }
}
