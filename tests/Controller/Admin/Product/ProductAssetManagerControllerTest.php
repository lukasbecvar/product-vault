<?php

namespace App\Tests\Controller\Admin\Product;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ProductAssetManagerControllerTest
 *
 * Test cases for product asset manager api endpoints
 *
 * @package App\Tests\Controller\Admin\Product
 */
class ProductAssetManagerControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test create product icon when request method is invalid
     *
     * @return void
     */
    public function testCreateProductIconWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/asset/icon/create');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test create product icon when api access token is not provided
     *
     * @return void
     */
    public function testCreateProductIconWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test create product icon when api access token is invalid
     *
     * @return void
     */
    public function testCreateProductIconWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [], [], [
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
     * Test create product icon when auth token is invalid
     *
     * @return void
     */
    public function testCreateProductIconWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [], [], [
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
     * Test create product icon when product is is not set
     *
     * @return void
     */
    public function testCreateProductIconWhenProductIsIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product icon when icon file is not set
     *
     * @return void
     */
    public function testCreateProductIconWhenIconFileIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [
            'product_id' => 5,
        ], [], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Icon file not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product icon when response is success
     *
     * @return void
     */
    public function testUpdateProductIconWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [
            'product_id' => 5,
        ], [
            'icon_file' => new UploadedFile(
                __DIR__ . '/../../../../src/DataFixtures/assets/icons/testing-icon.png',
                'test-icon',
                'image/png'
            )
        ], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product icon updated successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test create product icon when response is success
     *
     * @return void
     */
    public function testCreateProductIconWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/icon/create', [
            'product_id' => 1001,
        ], [
            'icon_file' => new UploadedFile(
                __DIR__ . '/../../../../src/DataFixtures/assets/icons/testing-icon.png',
                'test-icon',
                'image/png'
            )
        ], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product icon uploaded successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }

    /**
     * Test create product image when request method is invalid
     *
     * @return void
     */
    public function testCreateProductImageWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/asset/create/image');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test create product image when api access token is not provided
     *
     * @return void
     */
    public function testCreateProductImageWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [], [], [
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
     * Test create product image when api access token is invalid
     *
     * @return void
     */
    public function testCreateProductImageWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [], [], [
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
     * Test create product image when auth token is invalid
     *
     * @return void
     */
    public function testCreateProductImageWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [], [], [
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
     * Test create product image when product is is not set
     *
     * @return void
     */
    public function testCreateProductImageWhenProductIsIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product image when image file is not set
     *
     * @return void
     */
    public function testCreateProductImageWhenImageFileIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [
            'product_id' => 5,
        ], [], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Image file not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test create product image when response is success
     *
     * @return void
     */
    public function testCreateProductImageWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/product/asset/create/image', [
            'product_id' => 5,
        ], [
            'image_file' => new UploadedFile(
                __DIR__ . '/../../../../src/DataFixtures/assets/images/test-image-1.jpg',
                'test-image',
                'image/jpg'
            )
        ], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product image uploaded successfully!', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test delete product image when request method is invalid
     *
     * @return void
     */
    public function testDeleteProductImageWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/product/asset/image/delete');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test delete product image when api access token is not provided
     *
     * @return void
     */
    public function testDeleteProductImageWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [], [], [
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
     * Test delete product image when api access token is invalid
     *
     * @return void
     */
    public function testDeleteProductImageWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [], [], [
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
     * Test delete product image when auth token is invalid
     *
     * @return void
     */
    public function testDeleteProductImageWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [], [], [
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
     * Test delete product image when product is is not set
     *
     * @return void
     */
    public function testDeleteProductImageWhenProductIsIsNotSet(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Product id not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete product image when image id is not set
     *
     * @return void
     */
    public function testDeleteProductImageWhenImageIdIsNotSet(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [
            'product_id' => 5,
        ], [], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Parameter "image_file" not set.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test delete product image when response is success
     *
     * @return void
     */
    public function testDeleteProductImageWhenResponseIsSuccess(): void
    {
        $this->client->request('DELETE', '/api/admin/product/asset/image/delete', [
            'product_id' => 2,
            'image_file' => 'test-image-2.jpg',
        ], [], [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Product image: test-image-2.jpg deleted successfully', $responseData['message']);
        $this->assertArrayHasKey('product_data', $responseData);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
