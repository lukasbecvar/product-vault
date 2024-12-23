<?php

namespace App\Tests\Manager;

use App\Entity\Category;
use App\Manager\LogManager;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use App\Manager\CategoryManager;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoryManagerTest
 *
 * Test cases for category manager
 *
 * @package App\Test\Manager
 */
class CategoryManagerTest extends TestCase
{
    private CategoryManager $categoryManager;
    private LogManager & MockObject $logManager;
    private ErrorManager & MockObject $errorManager;
    private EntityManagerInterface & MockObject $entityManager;
    private CategoryRepository & MockObject $categoryRepository;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        // create category manager instance
        $this->categoryManager = new CategoryManager(
            $this->logManager,
            $this->errorManager,
            $this->entityManager,
            $this->categoryRepository
        );
    }

    /**
     * Test check if category name already exists
     *
     * @return void
     */
    public function testCheckIfCategoryNameAlreadyExists(): void
    {
        // simulate category exists
        $this->categoryRepository->method('findOneBy')
            ->with(['name' => 'Existing Category'])->willReturn(new Category());

        // call tested method
        $result = $this->categoryManager->checkIfCategoryNameAlreadyExists('Existing Category');

        // assert result
        $this->assertTrue($result);
    }

    /**
     * Test get category by id
     *
     * @return void
     */
    public function testGetCategoryById(): void
    {
        // mock category
        $category = new Category();
        $category->setName('Category 1');
        $this->categoryRepository->method('find')->with(1)->willReturn($category);

        // call tested method
        $result = $this->categoryManager->getCategoryById(1);

        // assert result
        $this->assertSame($category, $result);
    }

    /**
     * Test get category by name
     *
     * @return void
     */
    public function testGetCategoryByName(): void
    {
        // mock category
        $category = new Category();
        $category->setName('Category 1');
        $this->categoryRepository->method('findOneBy')
            ->with(['name' => 'Category 1'])->willReturn($category);

        // call tested method
        $result = $this->categoryManager->getCategoryByName('Category 1');

        // assert result
        $this->assertSame($category, $result);
    }

    /**
     * Test get categories list
     *
     * @return void
     */
    public function testGetCategoriesList(): void
    {
        // call tested method
        $result = $this->categoryManager->getCategoriesList();

        // assert result
        $this->assertIsArray($result);
    }

    /**
     * Test create category
     *
     * @return void
     */
    public function testCreateCategory(): void
    {
        // simulate category not exists
        $this->categoryRepository->method('findOneBy')->with(['name' => 'New Category'])
            ->willReturn(null);

        // expect entity persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Category::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Category created: New Category',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->categoryManager->createCategory('New Category');
    }

    /**
     * Test create category already exists
     *
     * @return void
     */
    public function testCreateCategoryAlreadyExists(): void
    {
        // simulate category exists
        $this->categoryRepository->method('findOneBy')->with(['name' => 'Existing Category'])
            ->willReturn(new Category());

        // expect error handler call
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Category name: Existing Category already exists',
            JsonResponse::HTTP_CONFLICT
        );

        // call tested method
        $this->categoryManager->createCategory('Existing Category');
    }

    /**
     * Test rename category
     *
     * @return void
     */
    public function testRenameCategory(): void
    {
        // mock category
        $category = new Category();
        $category->setName('Old Name');
        $this->categoryRepository->method('find')->with(1)->willReturn($category);

        // simulate category new name not exists
        $this->categoryRepository->method('findOneBy')->with(['name' => 'New Name'])->willReturn(null);

        // expect entity flush
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Category renamed: New Name',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->categoryManager->renameCategory(1, 'New Name');
    }

    /**
     * Test delete category
     *
     * @return void
     */
    public function testDeleteCategory(): void
    {
        // mock category
        $category = new Category();
        $category->setName('Category to Delete');
        $this->categoryRepository->method('find')->with(1)->willReturn($category);

        // expect entity remove and flush
        $this->entityManager->expects($this->once())->method('remove')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        // expect log action
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Category deleted: Category to Delete',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->categoryManager->deleteCategory(1);
    }
}
