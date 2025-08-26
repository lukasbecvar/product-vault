<?php

namespace App\Tests\Controller\Admin\Product;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductCreateControllerTest
 *
 * Test cases for create product api endpoint
 *
 * @package App\Tests\Controller\Admin\Product
 */
class ProductCreateControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test create product when request method is invalid
     *
     * @return void
     */
    public function testCreateProductWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/create');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test create product when auth token is not provided
     *
     * @return void
     */
    public function testCreateProductWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/create');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test create product when api access token is not provided
     *
     * @return void
     */
    public function testCreateProductWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
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
     * Test create product when auth token is invalid
     *
     * @return void
     */
    public function testCreateProductWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
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
     * Test create product when json input is not valid
     *
     * @return void
     */
    public function testCreateProductWhenJsonInputIsNotValid(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], ' {"test",}');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid JSON payload.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when name is empty
     *
     * @return void
     */
    public function testCreateProductWhenNameIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => '',
            'description' => 'Testing product description',
            'price' => '100',
            'price-currency' => 'USD',
            'categories' => ['Electronics', 'Home Appliances']
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('name: should not be blank., should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when description is empty
     *
     * @return void
     */
    public function testCreateProductWhenDescriptionIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => 'Testing product',
            'description' => '',
            'price' => '100',
            'price-currency' => 'USD',
            'categories' => ['Electronics', 'Home Appliances']
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('description: should not be blank., should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when price is empty
     *
     * @return void
     */
    public function testCreateProductWhenPriceIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => 'Testing product',
            'description' => 'Testing product description',
            'price' => '',
            'price-currency' => 'USD',
            'categories' => ['Electronics', 'Home Appliances']
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('price: should not be blank., should have at least 1 characters., This value should be of type numeric.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when price currency is empty
     *
     * @return void
     */
    public function testCreateProductWhenPriceCurrencyIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => 'Testing product',
            'description' => 'Testing product description',
            'price' => '100',
            'price-currency' => '',
            'categories' => ['Electronics', 'Home Appliances']
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('price-currency: should have at least 1 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when categories is empty
     *
     * @return void
     */
    public function testCreateProductWhenCategoriesIsEmpty(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => 'Testing product',
            'description' => 'Testing product description',
            'price' => '100',
            'price-currency' => 'USD',
            'categories' => []
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product requires minimal 1 category.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product when response is success
     *
     * @return void
     */
    public function testCreateProductWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'name' => 'Testing product',
            'description' => 'Testing product description',
            'price' => '100',
            'price-currency' => 'USD',
            'categories' => ['Electronics', 'Home Appliances'],
            'attributes' => [
                [
                    'name' => 'Color',
                    'attribute-value' => 'Red'
                ],
                [
                    'name' => 'Size',
                    'attribute-value' => 'XXL'
                ]
            ]
        ]) ?: null);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product: Testing product created successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }
}
