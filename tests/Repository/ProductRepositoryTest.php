<?php

namespace App\Tests\Repository;

use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ProductRepositoryTest
 *
 * Test cases for doctrine product repository
 *
 * @package App\Tests\Repository
 */
class ProductRepositoryTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private ProductRepository $productRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @phpstan-ignore-next-line */
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        // create product repository instance
        $this->productRepository = $this->entityManager->getRepository(Product::class);
    }

    /**
     * Test find products by filter criteria
     *
     * @return void
     */
    public function testFindByFilterCriteria(): void
    {
        // call test method
        $products = $this->productRepository->findByFilterCriteria('Testing product');

        // assert result
        $this->assertIsArray($products);
    }

    /**
     * Test get pagination info
     *
     * @return void
     */
    public function testGetPaginationInfo(): void
    {
        // call test method
        $paginationInfo = $this->productRepository->getPaginationInfo('Testing product');

        // assert result
        $this->assertIsArray($paginationInfo);
        $this->assertArrayHasKey('total_pages', $paginationInfo);
        $this->assertArrayHasKey('current_page_number', $paginationInfo);
        $this->assertArrayHasKey('total_items', $paginationInfo);
        $this->assertArrayHasKey('items_per_actual_page', $paginationInfo);
        $this->assertArrayHasKey('last_page_number', $paginationInfo);
        $this->assertIsInt($paginationInfo['total_pages']);
        $this->assertIsInt($paginationInfo['current_page_number']);
        $this->assertIsInt($paginationInfo['total_items']);
        $this->assertIsInt($paginationInfo['items_per_actual_page']);
        $this->assertIsInt($paginationInfo['last_page_number']);
        $this->assertArrayHasKey('is_next_page_exists', $paginationInfo);
        $this->assertArrayHasKey('is_previous_page_exists', $paginationInfo);
        $this->assertIsBool($paginationInfo['is_next_page_exists']);
        $this->assertIsBool($paginationInfo['is_previous_page_exists']);
    }

    /**
     * Test find products by search criteria
     *
     * @return void
     */
    public function testFindBySearchCriteria(): void
    {
        // call test method
        $products = $this->productRepository->findBySearchCriteria('Testing product');

        // assert result
        $this->assertIsArray($products);
    }

    /**
     * Test find products by categories
     *
     * @return void
     */
    public function testFindByCategories(): void
    {
        // call test method
        $products = $this->productRepository->findByCategories(['Electronics']);

        // assert result
        $this->assertInstanceOf(Product::class, $products[0]);
        $this->assertIsArray($products);
    }

    /**
     * Test find products by attributes
     *
     * @return void
     */
    public function testFindByAttributes(): void
    {
        // call test method
        $products = $this->productRepository->findByAttributes(['Color']);

        // assert result
        $this->assertInstanceOf(Product::class, $products[0]);
        $this->assertIsArray($products);
    }

    /**
     * Test find products by attributes values
     *
     * @return void
     */
    public function testFindByAttributesValues(): void
    {
        // call test method
        $products = $this->productRepository->findByAttributesValues(['Color' => 'Red']);

        // assert result
        $this->assertInstanceOf(Product::class, $products[0]);
        $this->assertIsArray($products);
    }

    /**
     * Test find products by attributes values with categories
     *
     * @return void
     */
    public function testFindByAttributesValuesWithCategories(): void
    {
        // call test method
        $products = $this->productRepository->findByAttributesValues(['Color' => 'Red'], ['Electronics']);

        // assert result
        $this->assertInstanceOf(Product::class, $products[0]);
        $this->assertIsArray($products);
    }

    /**
     * Test get product stats
     *
     * @return void
     */
    public function testGetProductStats(): void
    {
        // call test method
        $productStats = $this->productRepository->getProductStats();

        // assert result
        $this->assertIsArray($productStats);
        $this->assertArrayHasKey('total', $productStats);
        $this->assertArrayHasKey('active', $productStats);
        $this->assertArrayHasKey('inactive', $productStats);
        $this->assertIsInt($productStats['total']);
        $this->assertIsInt($productStats['active']);
        $this->assertIsInt($productStats['inactive']);
    }
}
