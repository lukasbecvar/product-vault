<?php

namespace App\Tests\User;

use App\Tests\CustomTestCase;
use Symfony\Component\String\ByteString;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserDataUpdateControllerTest
 *
 * Test cases for user data update controller (API endpoint)
 *
 * @package App\Tests\User
 */
class UserDataUpdateControllerTest extends CustomTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test update user password when request method is not valid
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenRequestMethodIsNotValid(): void
    {
        $this->client->request('GET', '/api/user/data/update/password');

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
     * Test update user password when auth token is not provided
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenAuthTokenIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password');

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
     * Test update user password when auth token is invalid
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenAuthTokenIsInvalid(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], ['HTTP_AUTHORIZATION' => 'Bearer invalid-token']);

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
     * Test update user password when new password is not provided
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenNewPasswordIsNotProvided(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
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
        $this->assertSame('Request body is empty.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user password when new password is empty
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenPasswordIsEmpty(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'new_password' => ''
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
        $this->assertSame('Parameter "new_password" is required!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user password when new password is too short
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenPasswordIsTooShort(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'new_password' => '1'
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
        $this->assertSame('Parameter "new_password" must be between 8 and 128 characters long!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user password when new password is too long
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenPasswordIsTooLong(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'new_password' => ByteString::fromRandom(130)
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
        $this->assertSame('Parameter "new_password" must be between 8 and 128 characters long!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test update user password when new password is valid
     *
     * @return void
     */
    public function testUpdateUserPasswordWhenNewPasswordIsValid(): void
    {
        $this->client->request('PATCH', '/api/user/data/update/password', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->generateJwtToken(),
        ], json_encode([
            'new_password' => 'testtest'
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
        $this->assertSame('Password updated successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
    }
}