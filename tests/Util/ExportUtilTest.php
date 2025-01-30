<?php

namespace Tests\Unit\Util;

use DateTime;
use App\Entity\Product;
use App\Util\ExportUtil;
use PHPUnit\Framework\TestCase;
use App\Repository\ProductRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ExportUtilTest
 *
 * Test cases for ExportUtil
 *
 * @package Tests\Unit\Util
 */
class ExportUtilTest extends TestCase
{
    private ExportUtil $exportUtil;
    private ProductRepository & MockObject $productRepository;

    protected function setUp(): void
    {
        // mock dependencies
        $this->productRepository = $this->createMock(ProductRepository::class);

        // create export util instance
        $this->exportUtil = new ExportUtil($this->productRepository);
    }

    /**
     * Test export product to json
     *
     * @return void
     */
    public function testExportToJson(): void
    {
        // create testing product mock
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Test Description');
        $product->method('getPrice')->willReturn('100.00');
        $product->method('getPriceCurrency')->willReturn('USD');
        $product->method('getAddedTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $product->method('getLastEditTime')->willReturn(new DateTime('2024-01-02 14:00:00'));
        $product->method('isActive')->willReturn(true);
        $product->method('getCategoriesRaw')->willReturn(['Category 1', 'Category 2']);
        $product->method('getProductAttributesRaw')->willReturn(['Attribute 1', 'Attribute 2']);
        $this->productRepository->method('findAll')->willReturn([$product]);

        // capture output buffer
        ob_start();
        $response = $this->exportUtil->exportToJson();
        $response->sendContent();
        $output = ob_get_clean();

        // assert response
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('attachment;filename="products-', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('application/json', $response->headers->get('content-type') ?? '');
    }

    /**
     * Test export product to xls
     *
     * @return void
     */
    public function testExportToXls(): void
    {
        // create testing product mock
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Test Description');
        $product->method('getPrice')->willReturn('100.00');
        $product->method('getPriceCurrency')->willReturn('USD');
        $product->method('getAddedTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $product->method('getLastEditTime')->willReturn(new DateTime('2024-01-02 14:00:00'));
        $product->method('isActive')->willReturn(true);
        $product->method('getCategoriesRaw')->willReturn(['Category 1', 'Category 2']);
        $product->method('getProductAttributesRaw')->willReturn(['Attribute 1', 'Attribute 2']);
        $this->productRepository->method('findAll')->willReturn([$product]);

        // capture output buffer
        ob_start();
        $response = $this->exportUtil->exportToXls();
        $response->sendContent();
        $output = ob_get_clean();

        // assert response
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('attachment;filename="products-', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type') ?? '');
    }

    /**
     * Test export product to xml
     *
     * @return void
     */
    public function testExportToXml(): void
    {
        // create testing product mock
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getDescription')->willReturn('Test Description');
        $product->method('getPrice')->willReturn('100.00');
        $product->method('getPriceCurrency')->willReturn('USD');
        $product->method('getAddedTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $product->method('getLastEditTime')->willReturn(new DateTime('2024-01-02 14:00:00'));
        $product->method('isActive')->willReturn(true);
        $product->method('getCategoriesRaw')->willReturn(['Category 1', 'Category 2']);
        $product->method('getProductAttributesRaw')->willReturn(['Attribute 1', 'Attribute 2']);
        $this->productRepository->method('findAll')->willReturn([$product]);

        // capture output buffer
        ob_start();
        $response = $this->exportUtil->exportToXml();
        $response->sendContent();
        $output = ob_get_clean();

        // assert response
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('attachment;filename="products-', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('application/xml', $response->headers->get('content-type') ?? '');
    }
}
