<?php

namespace App\Tests\Controller\Admin\Product;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductDeleteControllerTest
 *
 * Test cases for delete product controller
 *
 * @package App\Tests\Controller\Admin\Product
 */
class ProductDeleteControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test delete product when request method is not valid
     *
     * @return void
     */
    public function testDeleteProductWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/admin/product/delete');

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
     * Test delete product when auth token is not provided
     *
     * @return void
     */
    public function testDeleteProductWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete');

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product when auth token is invalid
     *
     * @return void
     */
    public function testDeleteProductWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer invalid-token'
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
        $this->assertSame('Invalid JWT Token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product when api access token is not provided
     *
     * @return void
     */
    public function testDeleteProductWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->generateJwtToken()
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
        $this->assertSame('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product when api access token is invalid
     *
     * @return void
     */
    public function testDeleteProductWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => 'invalid-token',
            'HTTP_Authorization' => 'Bearer ' . $this->generateJwtToken()
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
        $this->assertSame('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test delete product when product id is not provided
     *
     * @return void
     */
    public function testDeleteProductWhenProductIdIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_Authorization' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => ''
        ]) ?: null);

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
        $this->assertSame('Product id is not set or invalid', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete product when product not found
     *
     * @return void
     */
    public function testDeleteProductWhenProductNotFound(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_Authorization' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 123456789
        ]) ?: null);

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
        $this->assertSame('Product id: 123456789 not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test delete product when response is successful
     *
     * @return void
     */
    public function testDeleteProductWhenResponseIsSuccessful(): void
    {
        $this->client->request('DELETE', '/api/admin/product/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_Authorization' => 'Bearer ' . $this->generateJwtToken()
        ], json_encode([
            'product-id' => 146
        ]) ?: null);

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product data deleted successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
