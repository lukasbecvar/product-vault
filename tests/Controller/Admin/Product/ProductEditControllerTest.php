<?php

namespace App\Tests\Controller\Admin\Product;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductEditControllerTest
 *
 * Test cases for product edit controller
 *
 * @package App\Tests\Controller\Admin\Product
 */
class ProductEditControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test update product data when request method is not valid
     *
     * @return void
     */
    public function testUpdateProductDataWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/product/update');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update product data when auth token is not provided
     *
     * @return void
     */
    public function testUpdateProductDataWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product data when api access token is not provided
     *
     * @return void
     */
    public function testUpdateProductDataWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product data api access token is invalid
     *
     * @return void
     */
    public function testUpdateProductDataApiAccessTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => 'invalud-token',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product data when auth token is invalid
     *
     * @return void
     */
    public function testUpdateProductDataWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product data when product id is not set
     *
     * @return void
     */
    public function testUpdateProductDataWhenProductIdIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product data when product id is invalid
     *
     * @return void
     */
    public function testUpdateProductDataWhenProductIdIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 'invalid-id'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response data
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product data when product not found
     *
     * @return void
     */
    public function testUpdateProductDataWhenProductNotFound(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 999999999
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response data
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id: 999999999 not found.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update product data when update product data is successful
     *
     * @return void
     */
    public function testUpdateProductDataWhenUpdateProductDataIsSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'name' => 'Updated Product Name',
            'description' => 'Updated product description',
            'price' => '19.99',
            'price-currency' => 'USD'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response data
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product data updated successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test update product activity when request method is not valid
     *
     * @return void
     */
    public function testUpdateProductActivityWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/product/update/activity');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update product activity when auth token is not provided
     *
     * @return void
     */
    public function testUpdateProductActivityWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product activity when api access token is not provided
     *
     * @return void
     */
    public function testUpdateProductActivityWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product activity when auth token is invalid
     *
     * @return void
     */
    public function testUpdateProductActivityWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product activity when product id is not set
     *
     * @return void
     */
    public function testUpdateProductActivityWhenProductIdIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product activity when product id is invalid
     *
     * @return void
     */
    public function testUpdateProductActivityWhenProductIdIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 'invalid-id'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product activity when product not found
     *
     * @return void
     */
    public function testUpdateProductActivityWhenProductNotFound(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 999999999,
            'active' => 'true'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id: 999999999 not found.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update product activity when update product activity is successful
     *
     * @return void
     */
    public function testUpdateProductActivityWhenUpdateProductActivityIsSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/activity', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 113,
            'active' => 'false'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product data updated successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test update product categories when request method is not valid
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/product/update/categories');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update product categories when auth token is not provided
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product categories when api access token is not provided
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product categories when auth token is invalid
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product categories when product id is not set
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenProductIdIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product categories when product id is invalid
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenProductIdIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 'invalid-id'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product categories when process is not valid
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenProcessIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'invalid-process'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Process is not valid (allowed: add, remove).', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product categories when category list is not valid
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenCategoryListIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'add',
            'category-list' => []
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Category list not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product categories when update product categories is successful
     *
     * @return void
     */
    public function testUpdateProductCategoriesWhenUpdateProductCategoriesIsSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'add',
            'category-list' => ['Non assigned test category']
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product data updated successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test update product attribute when request method is not valid
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/product/update/attribute');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update product attribute when auth token is not provided
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product attribute when api access token is not provided
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product attribute when auth token is invalid
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test update product attribute when product id is not set
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenProductIdIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product attribute when product id is invalid
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenProductIdIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 'invalid-id'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Product id is not set or invalid.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product attribute when process is not valid
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenProcessIsNotValid(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'invalid-process'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Process is not valid (allowed: add, remove).', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product attribute when attribute name is not set
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenAttributeNameIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'add',
            'attribute-name' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Attribute name not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product attribute when attribute value is not set
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenAttributeValueIsNotSet(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'add',
            'attribute-name' => 'Color',
            'attribute-value' => ''
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Attribute value not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update product attribute when update product attribute is successful
     *
     * @return void
     */
    public function testUpdateProductAttributeWhenUpdateProductAttributeIsSuccessful(): void
    {
        $this->client->request('PATCH', '/api/admin/product/update/attribute', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 1,
            'process' => 'add',
            'attribute-name' => 'Non assigned test attribute',
            'attribute-value' => 'test-value'
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product data updated successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
