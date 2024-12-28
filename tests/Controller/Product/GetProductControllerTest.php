<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class GetProductControllerTest
 *
 * Test cases for get product API endpoint
 *
 * @package App\Tests\Controller\Product
 */
class GetProductControllerTest extends WebTestCase
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

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

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

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

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

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

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

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

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

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<mixed> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('product', $responseData);
        $this->assertArrayHasKey('id', $responseData['product']);
        $this->assertArrayHasKey('name', $responseData['product']);
        $this->assertArrayHasKey('description', $responseData['product']);
        $this->assertArrayHasKey('price', $responseData['product']);
        $this->assertArrayHasKey('priceCurrency', $responseData['product']);
        $this->assertArrayHasKey('active', $responseData['product']);
        $this->assertArrayHasKey('categories', $responseData['product']);
        $this->assertArrayHasKey('attributes', $responseData['product']);
        $this->assertArrayHasKey('icon', $responseData['product']);
        $this->assertArrayHasKey('images', $responseData['product']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
