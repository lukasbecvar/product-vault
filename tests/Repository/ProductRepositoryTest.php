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
}
