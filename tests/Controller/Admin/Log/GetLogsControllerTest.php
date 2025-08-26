<?php

namespace App\Tests\Controller\Admin\Log;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class GetLogsControllerTest
 *
 * Test cases for log manager controller
 *
 * @package App\Tests\Controller\Admin\Log
 */
class GetLogsControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get logs list when request method is invalid
     *
     * @return void
     */
    public function testGetLogsListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/logs');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get logs list when api access token is not provided
     *
     * @return void
     */
    public function testGetLogsListWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/logs', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get logs list when api access token is invalid
     *
     * @return void
     */
    public function testGetLogsListWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/logs', [], [], [
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
     * Test get logs list when auth token is invalid
     *
     * @return void
     */
    public function testGetLogsListWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/logs', [], [], [
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
     * Test get logs list when response is empty
     *
     * @return void
     */
    public function testGetLogsListWhenResponseIsEmpty(): void
    {
        $this->client->request('GET', '/api/admin/logs?status=NONEXISTENT&page=1', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertEquals('No logs found for specified filters', $responseData['message']);
        $this->assertEquals([
            'status' => 'NONEXISTENT',
            'page' => 1
        ], $responseData['current_filters']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test get logs list when response is success
     *
     * @return void
     */
    public function testGetLogsListWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/admin/logs?status=UNREADED&page=1', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('logs_data', $responseData['data']);
        $this->assertArrayHasKey('pagination_info', $responseData['data']);
        $this->assertArrayHasKey('stats', $responseData['data']);
        $this->assertArrayHasKey('name', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('message', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('status', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('time', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('user_agent', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('request_uri', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('request_method', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('ip_address', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('level', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('user_id', $responseData['data']['logs_data'][1]);
        $this->assertArrayHasKey('total_logs_count', $responseData['data']['pagination_info']);
        $this->assertArrayHasKey('current_page', $responseData['data']['pagination_info']);
        $this->assertArrayHasKey('total_pages_count', $responseData['data']['pagination_info']);
        $this->assertArrayHasKey('is_next_page_exists', $responseData['data']['pagination_info']);
        $this->assertArrayHasKey('is_previous_page_exists', $responseData['data']['pagination_info']);
        $this->assertArrayHasKey('last_page_number', $responseData['data']['pagination_info']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }

    /**
     * Test get logs statistics and count when request method is invalid
     *
     * @return void
     */
    public function testGetLogsStatsListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/logs/stats');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get logs statistics and count when api access token is not provided
     *
     * @return void
     */
    public function testGetLogsStatsListWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/logs/stats', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get logs statistics and count when api access token is invalid
     *
     * @return void
     */
    public function testGetLogsStatsListWhenApiAccessTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/logs/stats', [], [], [
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
     * Test get logs statistics and count when auth token is invalid
     *
     * @return void
     */
    public function testGetLogsStatsListWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/logs/stats', [], [], [
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
     * Test get logs statistics and count when response is success
     *
     * @return void
     */
    public function testGetLogsStatsListWhenResponseIsSuccess(): void
    {
        $this->client->request('GET', '/api/admin/logs/stats', [], [], [
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('success', $responseData['status']);
        $this->assertArrayHasKey('logs_count', $responseData['data']);
        $this->assertArrayHasKey('unreaded_logs_count', $responseData['data']);
        $this->assertArrayHasKey('readed_logs_count', $responseData['data']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
