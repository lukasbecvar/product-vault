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
     * Get all product categories with their database ids and names
     *
     * @return JsonResponse The status response in JSON
     */
    #[Tag(name: "Admin (product manager)")]
    #[Response(response: JsonResponse::HTTP_OK, description: "All product categories")]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: "The category list failed")]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/category/list', methods:['GET'], name: 'get_product_category_list')]
    public function getProductCategoryList(): JsonResponse
    {
        // get all categories
        try {
            $categories = $this->categoryManager->getCategoriesListRaw();
            return $this->json([
                'status' => 'success',
                'message' => 'All product categories',
                'categories' => $categories
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category list failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Create product category
     *
     * @param Request $request Request object
     *
     * @return JsonResponse The status response in JSON
     */
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
    #[Response(response: JsonResponse::HTTP_OK, description: "The category created successfully")]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: "The category create failed")]
    #[IsGranted('ROLE_ADMIN')]
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
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category create failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
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
    #[Tag(name: "Admin (product manager)")]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(properties: [
                    new Property(
                        property: "category_id",
                        type: "integer",
                        description: "Category id to rename",
                        example: 1
                    ),
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
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: "Category not found")]
    #[Response(response: JsonResponse::HTTP_CONFLICT, description: "Category name already exists")]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: "The category rename failed")]
    #[Response(response: JsonResponse::HTTP_OK, description: "The category renamed successfully")]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/category/rename', methods:['POST'], name: 'rename_product_category')]
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
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
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
    #[Tag(name: "Admin (product manager)")]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(properties: [
                    new Property(
                        property: "category_id",
                        type: "integer",
                        description: "Category id to delete",
                        example: 1
                    )
                ])
            )
        ]
    )]
    #[Response(response: JsonResponse::HTTP_OK, description: "The category deleted successfully")]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: "Category not found")]
    #[Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: "The category delete failed")]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/admin/product/category/delete', methods:['POST'], name: 'delete_product_category')]
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
                'message' => 'Category deleted successfully!',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category delete failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
