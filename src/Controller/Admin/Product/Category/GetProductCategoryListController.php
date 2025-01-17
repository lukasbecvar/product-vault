<?php

namespace App\Controller\Admin\Product\Category;

use Exception;
use OpenApi\Attributes\Tag;
use App\Manager\ErrorManager;
use App\Manager\CategoryManager;
use OpenApi\Attributes\Response;
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
    #[Response(response: JsonResponse::HTTP_OK, description: "All product categories")]
    #[Response(response: JsonResponse::HTTP_NOT_FOUND, description: "No product categories found")]
    #[Route('/api/admin/product/category/list', methods:['GET'], name: 'get_product_category_list')]
    public function getProductCategoryList(): JsonResponse
    {
        // get all categories
        try {
            $categories = $this->categoryManager->getCategoriesListRaw();
        } catch (Exception $e) {
            return $this->errorManager->handleError(
                message: 'Category list get failed',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
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
