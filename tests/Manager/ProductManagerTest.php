<?php

namespace App\Tests\Manager;

use Exception;
use App\Entity\Product;
use App\Manager\LogManager;
use App\Manager\ErrorManager;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductManagerTest
 *
 * Test cases for product manager
 *
 * @package App\Tests\Manager
 */
class ProductManagerTest extends TestCase
{
    private ProductManager $productManager;
    private LogManager & MockObject $logManager;
    private ErrorManager & MockObject $errorManager;
    private EntityManagerInterface & MockObject $entityManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // create product manager instance
        $this->productManager = new ProductManager(
            $this->logManager,
            $this->errorManager,
            $this->entityManager
        );
    }

    /**
     * Test create product with success result
     *
     * @return void
     */
    public function testCreateProductSuccess(): void
    {
        // testing product data
        $name = 'Test Product';
        $description = 'This is a test product.';
        $price = '19.99';
        $priceCurrency = 'USD';

        // expect entity manager to persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Product: ' . $name . ' created',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $product = $this->productManager->createProduct($name, $description, $price, $priceCurrency);

        // assert result
        $this->assertEquals($name, $product->getName());
        $this->assertEquals($description, $product->getDescription());
        $this->assertEquals($price, $product->getPrice());
        $this->assertEquals(strtoupper($priceCurrency), $product->getPriceCurrency());
        $this->assertTrue($product->isActive());
    }

    /**
     * Test create product with exception during persist
     *
     * @return void
     */
    public function testCreateProductFailure(): void
    {
        // testing product data
        $name = 'Test Product';
        $description = 'This is a test product.';
        $price = '19.99';
        $priceCurrency = 'USD';

        // simulate exception during persist
        $this->entityManager->expects($this->once())->method('persist')
            ->willThrowException(new Exception('Database error'));

        // expect error handling
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Product create error',
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            'Database error'
        );

        // call tested method
        $product = $this->productManager->createProduct($name, $description, $price, $priceCurrency);

        // assert result
        $this->assertEquals($name, $product->getName());
        $this->assertEquals($description, $product->getDescription());
        $this->assertEquals($price, $product->getPrice());
        $this->assertEquals(strtoupper($priceCurrency), $product->getPriceCurrency());
    }
}
