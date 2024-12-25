<?php

namespace App\Manager;

use Exception;
use App\Entity\Category;
use App\Entity\ProductCategory;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoryManager
 *
 * Manager for manipulating with product categories
 *
 * @package App\Manager
 */
class CategoryManager
{
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;

    public function __construct(
        LogManager $logManager,
        ErrorManager $errorManager,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository
    ) {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Check if category name already exists
     *
     * @param string $name The category name
     *
     * @return bool True if category name already exists, false otherwise
     */
    public function checkIfCategoryNameAlreadyExists(string $name): bool
    {
        return $this->categoryRepository->findOneBy(['name' => $name]) !== null;
    }

    /**
     * Get category entity object by id
     *
     * @param int $id The category id
     *
     * @return Category|null The category object or null if category not found
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * Get category entity object by name
     *
     * @param string $name The category name
     *
     * @return Category|null The category object or null if category not found
     */
    public function getCategoryByName(string $name): ?Category
    {
        return $this->categoryRepository->findOneBy(['name' => $name]);
    }

    /**
     * Get categories list
     *
     * @return string[] The categories list
     */
    public function getCategoriesList(): array
    {
        return $this->categoryRepository->findCategoryNames();
    }

    /**
     * Create category entity in database
     *
     * @param string $name The category name
     *
     * @return void
     */
    public function createCategory(string $name): void
    {
        // check if category name already exists
        if ($this->checkIfCategoryNameAlreadyExists($name)) {
            $this->errorManager->handleError(
                message: 'Category name: ' . $name . ' already exists',
                code: JsonResponse::HTTP_CONFLICT
            );
        }

        // create category entity
        $category = new Category();
        $category->setName($name);

        try {
            // save category to database
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to create category',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Category created: ' . $name,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Rename category
     *
     * @param int $id The category id
     * @param string $newName The new category name
     *
     * @return void
     */
    public function renameCategory(int $id, string $newName): void
    {
        // check if category name already exists
        if ($this->checkIfCategoryNameAlreadyExists($newName)) {
            $this->errorManager->handleError(
                message: 'Category name: ' . $newName . ' already exists',
                code: JsonResponse::HTTP_CONFLICT
            );
        }

        // get category by id
        $category = $this->getCategoryById($id);

        // check if category found
        if (!$category) {
            $this->errorManager->handleError(
                message: 'Category not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // rename category
        $category->setName($newName);

        try {
            // save category to database
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to rename category',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Category renamed: ' . $newName,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete category from database
     *
     * @param int $id The category id
     *
     * @return void
     */
    public function deleteCategory(int $id): void
    {
        // get category by id
        $category = $this->getCategoryById($id);

        // check if category found
        if ($category === null) {
            $this->errorManager->handleError(
                message: 'Category not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete category
        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete category',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action
        $this->logManager->saveLog(
            name: 'product-manager',
            message: 'Category deleted: ' . $category->getName(),
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Delete categories by product id
     *
     * @param int $productId The product id
     *
     * @return void
     */
    public function deleteCategoriesByProductId(int $productId): void
    {
        try {
            $this->entityManager->createQueryBuilder()
                ->delete(ProductCategory::class, 'pc')
                ->where('pc.product = :product_id')
                ->setParameter('product_id', $productId)
                ->getQuery()
                ->execute();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to delete categories by product id: ' . $productId,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
