<?php

namespace App\Tests\Controller\Admin\Product\Category;

use App\Tests\CustomTestCase;
use Symfony\Component\String\ByteString;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductCategoryManagerControllerTest
 *
 * Test cases for product category manager controller
 *
 * @package App\Tests\Controller\Admin\Product\Category
 */
class ProductCategoryManagerControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get all product categories when request method is invalid
     *
     * @return void
     */
    public function testGetAllProductCategoriesWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/list');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get all product categories when api access token is not provided
     *
     * @return void
     */
    public function testGetAllProductCategoriesWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/product/category/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get all product categories when api access token is invalid
     *
     * @return void
     */
    public function testGetAllProductCategoriesWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/category/list', [], [], [
            'HTTP_X_API_TOKEN' => 'invalud-token',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get all product categories when auth token is invalid
     *
     * @return void
     */
    public function testGetAllProductCategoriesWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/category/list', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertEquals('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get all product categories when response is success
     *
     * @return void
     */
    public function testGetAllProductCategoriesWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/admin/product/category/list', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('categories', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test create product category when request method is invalid
     *
     * @return void
     */
    public function testCreateProductCategoryWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/category/create');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test create product category when api access token is not provided
     *
     * @return void
     */
    public function testCreateProductCategoryWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/category/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test create product category when api access token is invalid
     *
     * @return void
     */
    public function testCreateProductCategoryWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => 'invalud-token',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test create product category when auth token is invalid
     *
     * @return void
     */
    public function testCreateProductCategoryWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertEquals('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test create product category when category name is not set
     *
     * @return void
     */
    public function testCreateProductCategoryWhenCategoryNameIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/category/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Category name not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product category when response is success
     *
     * @return void
     */
    public function testCreateProductCategoryWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/category/create', [
            'category_name' => 'New Category: ' . ByteString::fromRandom(6)->toString()
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Category created successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test rename product category when request method is invalid
     *
     * @return void
     */
    public function testRenameProductCategoryWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/category/rename');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test rename product category when api access token is not provided
     *
     * @return void
     */
    public function testRenameProductCategoryWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test rename product category when api access token is invalid
     *
     * @return void
     */
    public function testRenameProductCategoryWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => 'invalud-token',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test rename product category when auth token is invalid
     *
     * @return void
     */
    public function testRenameProductCategoryWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertEquals('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test rename product category when category id is not set
     *
     * @return void
     */
    public function testRenameProductCategoryWhenCategoryIdIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Category id not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test rename product category when category name is not set
     *
     * @return void
     */
    public function testRenameProductCategoryWhenCategoryNameIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [
            'category_id' => 1
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Category name not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test rename product category when response is success
     *
     * @return void
     */
    public function testRenameProductCategoryWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/category/rename', [
            'category_id' => 1,
            'category_name' => 'New Category ' . ByteString::fromRandom(16)->toString()
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Category renamed successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test delete product category when request method is invalid
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/category/delete');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test delete product category when api access token is not provided
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/category/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product category when api access token is invalid
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => 'invalud-token',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product category when auth token is invalid
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/category/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertEquals('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product category when category id is not set
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenCategoryIdIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/category/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Category id not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete product category when response is success
     *
     * @return void
     */
    public function testDeleteProductCategoryWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/category/delete', [
            'category_id' => 12
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Category deleted successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
