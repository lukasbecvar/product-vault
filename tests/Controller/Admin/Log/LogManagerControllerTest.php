<?php

namespace App\Tests\Controller\Admin\Log;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LogManagerController
 *
 * Test cases for log manager controller
 *
 * @package App\Tests\Controller\Admin\Log
 */
class LogManagerController extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test update log status when request method is invalid
     *
     * @return void
     */
    public function testUpdateLogStatusWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/log/status/update');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test update log status when api access token is not provided
     *
     * @return void
     */
    public function testUpdateLogStatusWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [], [], [
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
     * Test update log status when api access token is invalid
     *
     * @return void
     */
    public function testUpdateLogStatusWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [], [], [
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
     * Test update log status when auth token is invalid
     *
     * @return void
     */
    public function testUpdateLogStatusWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [], [], [
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
     * Test update log status when log id is not set
     *
     * @return void
     */
    public function testUpdateLogStatusWhenLogIdIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [
            'id' => '',
            'status' => ''
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Log id not set!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update log status when new log status is not set
     *
     * @return void
     */
    public function testUpdateLogStatusWhenNewLogStatusIsNotSet(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [
            'id' => 1,
            'status' => ''
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('New log status not set!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update log status when log not found
     *
     * @return void
     */
    public function testUpdateLogStatusWhenLogNotFound(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [
            'id' => 33333,
            'status' => 'READED'
        ], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Error to get log by id: 33333', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update log status when response is success
     *
     * @return void
     */
    public function testUpdateLogStatusWhenResponseIsSuccess(): void
    {
        $this->client->request('POST', '/api/admin/log/status/update', [
            'id' => 1,
            'status' => 'READED'
        ], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Log status updated successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
