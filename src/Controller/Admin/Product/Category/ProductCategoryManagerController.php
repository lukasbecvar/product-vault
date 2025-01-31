<?php

namespace App\Controller\Admin\Product\Category;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Schema;
use OpenApi\Attributes\Property;
use App\Manager\CategoryManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\RequestBody;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ProductCategoryManagerController
 *
 * Controller for managing product categories
 *
 * @package App\Controller\Admin\Product\Category
 */
class ProductCategoryManagerController extends AbstractController
{
    private ErrorManager $errorManager;
    private CategoryManager $categoryManager;

    public function __construct(ErrorManager $errorManager, CategoryManager $categoryManager)
    {
        $this->errorManager = $errorManager;
        $this->categoryManager = $categoryManager;
    }

    /**
     * Create product category
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(properties: [
                    new Property(
                        property: "category_name",
                        type: "string",
                        description: "Category name",
                        example: "Category name"
                    )
                ])
            )
        ]
    )]
    #[Response(
        response: JsonResponse::HTTP_CONFLICT,
        description: "Category already exists",
        content: new JsonContent(
            example: [
                'status' => 'error',
                'message' => 'Category already exists'
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_CREATED,
        description: "The category created successfully",
        content: new JsonContent(
            example: [
                'status' => 'success',
                'message' => 'Category created successfully'
            ]
        )
    )]
    #[Route('/api/admin/product/category/create', methods:['POST'], name: 'create_product_category')]
    public function createProductIcon(Request $request): JsonResponse
    {
        // get request parameters
        $categoryName = (string) $request->request->get('category_name');

        // check if category name is set
        if ($categoryName == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category name not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // create category
        try {
            $this->categoryManager->createCategory($categoryName);
            return $this->json([
                'status' => 'success',
                'message' => 'Category created successfully!',
            ], JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category create failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Rename product category
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[Parameter(name: 'category_id', in: 'query', description: 'Category id to rename', required: true)]
    #[Parameter(name: 'category_name', in: 'query', description: 'Category new name', required: true)]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Category not found",
        content: new JsonContent(
            example: [
                'status' => 'error',
                'message' => 'Category not found'
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_CONFLICT,
        description: "Category name already exists",
        content: new JsonContent(
            example: [
                'status' => 'error',
                'message' => 'Category name already exists'
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: "The category renamed successfully",
        content: new JsonContent(
            example: [
                'status' => 'success',
                'message' => 'Category renamed successfully'
            ]
        )
    )]
    #[Route('/api/admin/product/category/rename', methods:['PATCH'], name: 'rename_product_category')]
    public function renameProductCategory(Request $request): JsonResponse
    {
        // get request parameters
        $categoryId = (int) $request->request->get('category_id');
        $categoryName = (string) $request->request->get('category_name');

        // check if category id is set
        if ($categoryId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category id not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if category name is set
        if ($categoryName == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category name not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // rename category
        try {
            $this->categoryManager->renameCategory($categoryId, $categoryName);
            return $this->json([
                'status' => 'success',
                'message' => 'Category renamed successfully!',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category rename failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }

    /**
     * Delete product category from database
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[Parameter(name: 'category_id', in: 'query', description: 'Category id to delete', required: true)]
    #[Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Category ID not set",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Category id not set")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Category not found",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "Category not found")
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: "The category deleted successfully",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "Category deleted successfully")
            ]
        )
    )]
    #[Route('/api/admin/product/category/delete', methods:['DELETE'], name: 'delete_product_category')]
    public function deleteProductCategory(Request $request): JsonResponse
    {
        // get request parameters
        $categoryId = (int) $request->request->get('category_id');

        // check if category id is set
        if ($categoryId == null) {
            return $this->json([
                'status' => 'error',
                'message' => 'Category id not set.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // delete category
        try {
            $this->categoryManager->deleteCategory($categoryId);
            return $this->json([
                'status' => 'success',
                'message' => 'Category deleted success!',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category delete failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }
    }
}
