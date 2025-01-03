<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProductGetControllerTest
 *
 * Test cases for get product API endpoint
 *
 * @package App\Tests\Controller\Product
 */
class ProductGetControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get product by id when request method is invalid
     *
     * @return void
     */
    public function testGetProductByIdWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/get?id=1');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get product by id when api access token is not provided
     *
     * @return void
     */
    public function testGetProductByIdWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/product/get?id=1');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product by id when api access token is invalid
     *
     * @return void
     */
    public function testGetProductByIdWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/product/get?id=1', [], [], [
            'HTTP_X_API_TOKEN' => 'invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product by id when product not found
     *
     * @return void
     */
    public function testGetProductByIdWhenProductNotFound(): void
    {
        $this->client->request('GET', '/api/product/get?id=1000000', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Product id: 1000000 not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test get product by id when response is success
     *
     * @return void
     */
    public function testGetProductByIdWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/get?id=1', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('name', $responseData['data']);
        $this->assertArrayHasKey('description', $responseData['data']);
        $this->assertArrayHasKey('price', $responseData['data']);
        $this->assertArrayHasKey('priceCurrency', $responseData['data']);
        $this->assertArrayHasKey('active', $responseData['data']);
        $this->assertArrayHasKey('categories', $responseData['data']);
        $this->assertArrayHasKey('attributes', $responseData['data']);
        $this->assertArrayHasKey('icon', $responseData['data']);
        $this->assertArrayHasKey('images', $responseData['data']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test get product list by filter when request method is invalid
     *
     * @return void
     */
    public function testGetProductListByFilterWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/product/list', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get product list by filter when api access token is not provided
     *
     * @return void
     */
    public function testGetProductListByFilterWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/product/list', [], [], [
            'HTTP_X_API_TOKEN' => 'invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product list by filter when api access token is invalid
     *
     * @return void
     */
    public function testGetProductListByFilterWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/list', [], [], [
            'HTTP_X_API_TOKEN' => 'invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product list by filter when response is success
     *
     * @return void
     */
    public function testGetProductListByFilterWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/product/list', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ], json_encode([]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('products_data', $responseData);
        $this->assertArrayHasKey('id', $responseData['products_data'][0]);
        $this->assertArrayHasKey('name', $responseData['products_data'][0]);
        $this->assertArrayHasKey('description', $responseData['products_data'][0]);
        $this->assertArrayHasKey('price', $responseData['products_data'][0]);
        $this->assertArrayHasKey('priceCurrency', $responseData['products_data'][0]);
        $this->assertArrayHasKey('active', $responseData['products_data'][0]);
        $this->assertArrayHasKey('categories', $responseData['products_data'][0]);
        $this->assertArrayHasKey('attributes', $responseData['products_data'][0]);
        $this->assertArrayHasKey('icon', $responseData['products_data'][0]);
        $this->assertArrayHasKey('images', $responseData['products_data'][0]);
        $this->assertArrayHasKey('total_pages', $responseData['pagination_info']);
        $this->assertArrayHasKey('current_page_number', $responseData['pagination_info']);
        $this->assertArrayHasKey('total_items', $responseData['pagination_info']);
        $this->assertArrayHasKey('items_per_actual_page', $responseData['pagination_info']);
        $this->assertArrayHasKey('last_page_number', $responseData['pagination_info']);
        $this->assertArrayHasKey('is_next_page_exists', $responseData['pagination_info']);
        $this->assertArrayHasKey('is_previous_page_exists', $responseData['pagination_info']);
        $this->assertArrayHasKey('pagination_info', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
