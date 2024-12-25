<?php

namespace App\Tests\Manager;

use Exception;
use App\Entity\Product;
use App\Manager\LogManager;
use App\Manager\ErrorManager;
use App\Manager\ProductManager;
use PHPUnit\Framework\TestCase;
use App\Manager\CategoryManager;
use App\Manager\AttributeManager;
use App\Repository\ProductRepository;
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
    private CategoryManager & MockObject $categoryManager;
    private AttributeManager & MockObject $attributeManager;
    private ProductRepository & MockObject $productRepository;
    private EntityManagerInterface & MockObject $entityManager;

    public function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->categoryManager = $this->createMock(CategoryManager::class);
        $this->attributeManager = $this->createMock(AttributeManager::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // create product manager instance
        $this->productManager = new ProductManager(
            $this->logManager,
            $this->errorManager,
            $this->categoryManager,
            $this->attributeManager,
            $this->productRepository,
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

    /**
     * Test edit product with success result
     *
     * @return void
     */
    public function testEditProductSuccess(): void
    {
        // testing product data
        $productId = 1;
        $name = 'Updated Test Product';
        $description = 'Updated description.';
        $price = '29.99';
        $priceCurrency = 'USD';

        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // expect entity manager to persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Product: Test Product edited',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->productManager->editProduct($productId, $name, $description, $price, $priceCurrency);
    }

    /**
     * Test edit product with exception during persist
     *
     * @return void
     */
    public function testEditProductFailure(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // simulate exception during persist
        $this->entityManager->expects($this->once())->method('persist')
            ->willThrowException(new Exception('Database error'));

        // expect error handling
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Product edit error',
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            'Database error'
        );

        // call tested method
        $this->productManager->editProduct(1, 'New Product Name', 'New product description', '15.00', 'USD');
    }

    /**
     * Test delete product with success result
     *
     * @return void
     */
    public function testDeleteProductSuccess(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();
        $product->method('isActive')->willReturn(true);

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // expect entity manager to persist and flush
        $this->entityManager->expects($this->once())->method('remove')->with($product);
        $this->entityManager->expects($this->once())->method('flush');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Product: Test Product with id: 1 deleted',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->productManager->deleteProduct(1);
    }

    /**
     * Test delete product with exception during remove
     *
     * @return void
     */
    public function testDeleteProductFailure(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();
        $product->method('isActive')->willReturn(true);

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // simulate exception during persist
        $this->entityManager->expects($this->once())->method('remove')->with($product)
            ->willThrowException(new Exception('Database error'));

        // expect error handling
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Product delete error id: 1',
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            'Database error'
        );

        // call tested method
        $this->productManager->deleteProduct(1);
    }

    /**
     * Test activate product with success result
     *
     * @return void
     */
    public function testActivateProductSuccess(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();
        $product->method('isActive')->willReturn(false);

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // expect entity manager to persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Product: Test Product activated',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->productManager->activateProduct(1);
    }

    /**
     * Test activate product when product is already active
     *
     * @return void
     */
    public function testActivateProductAlreadyActive(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();
        $product->method('isActive')->willReturn(true);

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // expect error handling
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Product id: 1 is already active',
            JsonResponse::HTTP_BAD_REQUEST
        );

        // call tested method
        $this->productManager->activateProduct(1);
    }

    /**
     * Test deactivate product with success result
     *
     * @return void
     */
    public function testDeactivateProductSuccess(): void
    {
        // create a product mock to return from repository
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Old description');
        $product->method('getPrice')->willReturn('19.99');
        $product->method('getPriceCurrency')->willReturn('EUR');
        $product->method('setName')->willReturnSelf();
        $product->method('setDescription')->willReturnSelf();
        $product->method('setPrice')->willReturnSelf();
        $product->method('setPriceCurrency')->willReturnSelf();
        $product->method('isActive')->willReturn(true);

        // mock product find method
        $this->productRepository->method('find')->willReturn($product);

        // expect entity manager to persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())->method('flush');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'product-manager',
            'Product: Test Product deactivated',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->productManager->deactivateProduct(1);
    }

    /**
     * Test deactivate product when product is already inactive
     *
     * @return void
     */
    public function testDeactivateProductAlreadyInactive(): void
    {
        // testing product data
        $product = new Product();
        $product->setActive(false);
        $this->productRepository->method('find')->willReturn($product);

        // expect error handling
        $this->errorManager->expects($this->once())->method('handleError')->with(
            'Product id: 1 is already inactive',
            JsonResponse::HTTP_BAD_REQUEST
        );

        // call tested method
        $this->productManager->deactivateProduct(1);
    }
}
