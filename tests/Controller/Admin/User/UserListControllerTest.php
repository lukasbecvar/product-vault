<?php

namespace App\Tests\Controller\Admin\User;

use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserListControllerTest
 *
 * Test cases for user list API endpoint
 *
 * @package App\Tests\Controller\Admin\User
 */
class UserListControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test get users list when request method is invalid
     *
     * @return void
     */
    public function testGetUsersListWhenRequestMethodIsInvalid(): void
    {
        $this->client->request('POST', '/api/admin/user/list');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test get users list when auth token is not provided
     *
     * @return void
     */
    public function testGetUsersListWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/user/list');

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('JWT Token not found', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get users list when api access token is not provided
     *
     * @return void
     */
    public function testGetUsersListWhenApiAccessTokenIsNotProvided(): void
    {
        $this->client->request('GET', '/api/admin/user/list', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        // assert response
        $this->assertSame('Invalid access token', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Test get users list when auth token is invalid
     *
     * @return void
     */
    public function testGetUsersListWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('GET', '/api/admin/user/list', [], [], [
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
     * Test get users list success
     *
     * @return void
     */
    public function testGetUsersListSuccess(): void
    {
        $this->client->request('GET', '/api/admin/user/list', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['API_TOKEN'],
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken()
        ]);

        /** @var array<mixed> $responseData */
        $responseData = json_decode(($this->client->getResponse()->getContent() ?: '{}'), true);

        /** @var array<mixed> $user */
        $user = $responseData['users'][0];

        // assert response
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('users', $responseData);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('first-name', $user);
        $this->assertArrayHasKey('last-name', $user);
        $this->assertArrayHasKey('roles', $user);
        $this->assertArrayHasKey('register-time', $user);
        $this->assertArrayHasKey('last-login-time', $user);
        $this->assertArrayHasKey('ip-address', $user);
        $this->assertArrayHasKey('browser', $user);
        $this->assertArrayHasKey('status', $user);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}
