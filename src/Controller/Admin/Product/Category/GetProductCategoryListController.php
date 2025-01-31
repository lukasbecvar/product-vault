<?php

namespace App\Controller\Admin\Product\Category;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Response;
use App\Manager\CategoryManager;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class GetProductCategoryListController
 *
 * Controller for get list of product categories
 *
 * @package App\Controller\Admin\Product\Category
 */
class GetProductCategoryListController extends AbstractController
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
    #[IsGranted('ROLE_ADMIN')]
    #[Tag(name: "Admin (product manager)")]
    #[Response(
        response: JsonResponse::HTTP_OK,
        description: "All product categories",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "success"),
                new Property(property: "message", type: "string", example: "All product categories"),
                new Property(
                    property: "categories",
                    type: "array",
                    items: new Items(
                        type: "object",
                        properties: [
                            new Property(property: "id", type: "integer", example: 1),
                            new Property(property: "name", type: "string", example: "Category 1")
                        ]
                    )
                )
            ]
        )
    )]
    #[Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "No product categories found",
        content: new JsonContent(
            type: "object",
            properties: [
                new Property(property: "status", type: "string", example: "error"),
                new Property(property: "message", type: "string", example: "No product categories found")
            ]
        )
    )]
    #[Route('/api/admin/product/category/list', methods:['GET'], name: 'get_product_category_list')]
    public function getProductCategoryList(): JsonResponse
    {
        // get all categories
        try {
            $categories = $this->categoryManager->getCategoriesListRaw();
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category list get failed',
                exceptionMessage: $e->getMessage(),
                code: ($e->getCode() === 0 ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode())
            );
        }

        // check if categories are empty
        if (empty($categories)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No product categories found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // return categories list
        return $this->json([
            'status' => 'success',
            'message' => 'All product categories',
            'categories' => $categories
        ], JsonResponse::HTTP_OK);
    }
}
