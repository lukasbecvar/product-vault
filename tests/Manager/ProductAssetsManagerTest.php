<?php

namespace App\Tests\Manager;

use App\Entity\Product;
use App\Util\StorageUtil;
use App\Manager\LogManager;
use App\Entity\ProductIcon;
use App\Entity\ProductImage;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use App\Manager\ProductAssetsManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductIconRepository;
use App\Repository\ProductImageRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductAssetsManagerTest
 *
 * Test cases for product assets manager
 *
 * @package App\Tests\Manager
 */
class ProductAssetsManagerTest extends TestCase
{
    private LogManager & MockObject $logManager;
    private StorageUtil & MockObject $storageUtil;
    private ErrorManager & MockObject $errorManager;
    private ProductAssetsManager $productAssetsManager;
    private EntityManagerInterface & MockObject $entityManager;
    private ProductIconRepository & MockObject $productIconRepository;
    private ProductImageRepository & MockObject $productImageRepository;

    public function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->storageUtil = $this->createMock(StorageUtil::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productIconRepository = $this->createMock(ProductIconRepository::class);
        $this->productImageRepository = $this->createMock(ProductImageRepository::class);

        // create product assets manager instance
        $this->productAssetsManager = new ProductAssetsManager(
            $this->logManager,
            $this->storageUtil,
            $this->errorManager,
            $this->entityManager,
            $this->productIconRepository,
            $this->productImageRepository
        );
    }

    /**
     * Test get product icons list
     *
     * @return void
     */
    public function testGetProductIconsList(): void
    {
        // mock product icons
        $icons = [new ProductIcon(), new ProductIcon()];
        $this->productIconRepository->expects($this->once())->method('findAll')->willReturn($icons);

        // call tested method
        $result = $this->productAssetsManager->getProductIconsList();

        // assert result
        $this->assertCount(2, $result);
        $this->assertIsArray($result);
    }

    /**
     * Test get product icon by id
     *
     * @return void
     */
    public function testGetProductIconById(): void
    {
        // mock product icon
        $icon = new ProductIcon();
        $this->productIconRepository->expects($this->once())->method('find')->with(1)->willReturn($icon);

        // call tested method
        $result = $this->productAssetsManager->getProductIconById(1);

        // assert result
        $this->assertInstanceOf(ProductIcon::class, $result);
    }

    /**
     * Test get product icon by file name
     *
     * @return void
     */
    public function testGetProductIconByFileName(): void
    {
        // mock product icon
        $icon = new ProductIcon();
        $this->productIconRepository->expects($this->once())->method('findOneBy')
            ->with(['icon_file' => 'example.png'])->willReturn($icon);

        // call tested method
        $result = $this->productAssetsManager->getProductIconByFileName('example.png');

        // assert result
        $this->assertInstanceOf(ProductIcon::class, $result);
    }

    /**
     * Test create product icon
     *
     * @return void
     */
    public function testCreateProductIcon(): void
    {
        $iconPath = 'src/DataFixtures/assets/icons/testing-icon.png';
        $product = new Product();

        // expect create storage resource call
        $this->storageUtil->expects($this->once())->method('createStorageResource');

        // expect entity persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(ProductIcon::class));
        $this->entityManager->expects($this->once())->method('flush');

        // call tested method
        $this->productAssetsManager->createProductIcon($iconPath, $product);
    }

    /**
     * Test get product images list
     *
     * @return void
     */
    public function testGetProductImagesList(): void
    {
        // mock product images
        $images = [new ProductImage(), new ProductImage()];
        $this->productImageRepository->expects($this->once())->method('findAll')->willReturn($images);

        // call tested method
        $result = $this->productAssetsManager->getProductImagesList();

        // assert result
        $this->assertCount(2, $result);
        $this->assertIsArray($result);
    }

    /**
     * Test get product image by id
     *
     * @return void
     */
    public function testGetProductImageById(): void
    {
        // mock product image
        $image = new ProductImage();
        $this->productImageRepository->expects($this->once())->method('find')->with(1)->willReturn($image);

        // call tested method
        $result = $this->productAssetsManager->getProductImageById(1);

        // assert result
        $this->assertInstanceOf(ProductImage::class, $result);
    }

    /**
     * Test get product image by file name
     *
     * @return void
     */
    public function testGetProductImageByFileName(): void
    {
        // mock product image
        $image = new ProductImage();
        $this->productImageRepository->expects($this->once())->method('findOneBy')
            ->with(['image_file' => 'example.jpg'])->willReturn($image);

        // call tested method
        $result = $this->productAssetsManager->getProductImageByFileName('example.jpg');

        // assert result
        $this->assertInstanceOf(ProductImage::class, $result);
    }

    /**
     * Test create product image
     *
     * @return void
     */
    public function testCreateProductImage(): void
    {
        $imagePath = 'src/DataFixtures/assets/images/test-image-1.jpg';
        $product = new Product();

        // expect create storage resource call
        $this->storageUtil->expects($this->once())->method('createStorageResource');

        // expect entity persist and flush
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(ProductImage::class));
        $this->entityManager->expects($this->once())->method('flush');

        // call tested method
        $this->productAssetsManager->createProductImage($imagePath, $product);
    }

    /**
     * Test delete product image
     *
     * @return void
     */
    public function testDeleteProductImage(): void
    {
        // mock product image
        $image = new ProductImage();
        $image->setImageFile('image_123.jpg');
        $this->productImageRepository->expects($this->once())->method('find')->with(1)->willReturn($image);

        // expect delete storage resource call
        $this->storageUtil->expects($this->once())->method('deleteStorageResource')->with('images', 'image_123.jpg');

        // expect entity remove and flush
        $this->entityManager->expects($this->once())->method('remove')->with($image);
        $this->entityManager->expects($this->once())->method('flush');

        // call tested method
        $this->productAssetsManager->deleteProductImage(1);
    }

    /**
     * Test generate asset name
     *
     * @return void
     */
    public function testGenerateAssetName(): void
    {
        $subPath = 'icons';
        $resourceName = 'icon.png';

        // expect check if assets exist call
        $this->storageUtil->expects($this->once())->method('checkIfAssetsExist')->willReturn(false);

        // expect check if assets exist call
        $this->storageUtil->expects($this->once())->method('checkIfAssetsExist')
            ->willReturnOnConsecutiveCalls(true, false);

        // call tested method
        $name = $this->productAssetsManager->generateAssetName($subPath, $resourceName);

        // assert result
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }
}
