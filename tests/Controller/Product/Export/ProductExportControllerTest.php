<?php

namespace App\Tests\Controller\Product\Export;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ProductExportControllerTest
 *
 * Test cases for product export API endpoints
 *
 * @package App\Tests\Controller\Product\Export
 */
class ProductExportControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get product list xls export when request method is invalid
     *
     * @return void
     */
    public function testGetProductListXlsExportWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/product/export/xls');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(StreamedResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get product list xls export when api access token is not provided
     *
     * @return void
     */
    public function testGetProductListXlsExportWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/product/export/xls');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(StreamedResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product list xls export when api access token is invalid
     *
     * @return void
     */
    public function testGetProductListXlsExportWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/product/export/xls', [], [], [
            'HTTP_X_API_TOKEN' => 'invalid-token'
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertEquals('Invalid access token.', $responseData['message']);
        $this->assertResponseStatusCodeSame(StreamedResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get product list xls export when response is success
     *
     * @return void
     */
    public function testGetProductListXlsExportWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/product/export/xls', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN']
        ]);

        // get response
        $response = $this->client->getResponse();

        // assert response
        $this->assertResponseStatusCodeSame(StreamedResponse::HTTP_OK);
        $this->assertStringContainsString('attachment;filename="products-', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type') ?? '');
    }
}
