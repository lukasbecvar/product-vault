<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProductAssetControllerTest
 *
 * Test cases for product asset get API endpoints
 *
 * @package App\Tests\Controller\Product
 */
class ProductAssetControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get product icon when request method is invalid
     *
     * @return void
     */
    public function testGetProductIconWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/asset/icon');

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
     * Test get product icon when api access token is not provided
     *
     * @return void
     */
    public function testGetProductIconWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/product/asset/icon?icon_file=testing-icon.png');

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
     * Test get product icon when api access token is invalid
     *
     * @return void
     */
    public function testGetProductIconWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/product/asset/icon?icon_file=testing-icon.png', [], [], [
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
     * Test get product icon when icon file is not set
     *
     * @return void
     */
    public function testGetProductIconWhenIconFileIsNotSet(): void
    {
        $this->client->request('GET', '/api/product/asset/icon', [], [], [
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
        $this->assertEquals('Icon file not set', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test get product icon when icon file not found
     *
     * @return void
     */
    public function testGetProductIconWhenIconFileNotFound(): void
    {
        $this->client->request('GET', '/api/product/asset/icon?icon_file=not-found-icon.png', [], [], [
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
        $this->assertEquals('Product icon not found: not-found-icon.png', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test get product icon when response is success
     *
     * @return void
     */
    public function testGetProductIconWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/asset/icon?icon_file=testing-icon.png', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        // get response
        $response = $this->client->getResponse();
        $contentLength = $response->headers->get('Content-Length');

        // assert response
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
        $this->assertTrue($response->headers->contains('Content-Type', 'image/png'));
        $this->assertTrue($response->headers->has('Content-Disposition'));
        $this->assertNotNull($contentLength);
        $this->assertGreaterThan(0, (int) $contentLength);
    }

    /**
     * Test get product image when request method is invalid
     *
     * @return void
     */
    public function testGetProductImageWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/asset/image');

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
     * Test get product image when api access token is not provided
     *
     * @return void
     */
    public function testGetProductImageWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/product/asset/image?image_file=test-image-1.jpg');

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
     * Test get product image when api access token is invalid
     *
     * @return void
     */
    public function testGetProductImageWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/product/asset/image?image_file=test-image-1.jpg', [], [], [
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
     * Test get product image when image file is not set
     *
     * @return void
     */
    public function testGetProductImageWhenImageFileIsNotSet(): void
    {
        $this->client->request('GET', '/api/product/asset/image', [], [], [
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
        $this->assertEquals('Image file not set', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test get product image when image file not found
     *
     * @return void
     */
    public function testGetProductImageWhenImageFileNotFound(): void
    {
        $this->client->request('GET', '/api/product/asset/image?image_file=not-found-image.jpg', [], [], [
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
        $this->assertEquals('Product image not found: not-found-image.jpg', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test get product image when response is success
     *
     * @return void
     */
    public function testGetProductImageWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/asset/image?image_file=test-image-1.jpg', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        // get response
        $response = $this->client->getResponse();
        $contentLength = $response->headers->get('Content-Length');

        // assert response
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
        $this->assertTrue($response->headers->contains('Content-Type', 'image/jpg'));
        $this->assertTrue($response->headers->has('Content-Disposition'));
        $this->assertNotNull($contentLength);
        $this->assertGreaterThan(0, (int) $contentLength);
    }
}
