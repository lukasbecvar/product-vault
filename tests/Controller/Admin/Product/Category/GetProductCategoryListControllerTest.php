<?php

namespace App\Tests\Controller\Admin\Product\Category;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class GetProductCategoryListControllerTest
 *
 * Test cases for categories get controller
 *
 * @package App\Tests\Controller\Admin\Product\Category
 */
class GetProductCategoryListControllerTest extends CustomTestCase
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
}
