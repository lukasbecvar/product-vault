<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProductStatsControllerTest
 *
 * Test cases for product stats API endpoint
 *
 * @package App\Tests\Controller\Product
 */
class ProductStatsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get product stats when request method is invalid
     *
     * @return void
     */
    public function testGetProductStatsWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/stats');

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
     * Test get product stats when response is success
     *
     * @return void
     */
    public function testGetProductStatsWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/stats', [], [], [
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
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('total', $responseData['data']);
        $this->assertArrayHasKey('active', $responseData['data']);
        $this->assertArrayHasKey('inactive', $responseData['data']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test get product categories list when request method is invalid
     *
     * @return void
     */
    public function testGetProductCategoriesListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/categories');

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
     * Test get product categories list when response is success
     *
     * @return void
     */
    public function testGetProductCategoriesListWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/categories', [], [], [
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
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test get product attributes list when request method is invalid
     *
     * @return void
     */
    public function testGetProductAttributesListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/attributes');

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
     * Test get product attributes list when response is success
     *
     * @return void
     */
    public function testGetProductAttributesListWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/attributes', [], [], [
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
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
