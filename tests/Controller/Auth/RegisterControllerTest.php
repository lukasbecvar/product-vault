<?php

namespace App\Tests\Controller\Auth;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegisterControllerTest
 *
 * Test cases for user registration controller (API endpoint)
 *
 * @package App\Tests\Controller\Auth
 */
class RegisterControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // enable registration API component
        $_ENV['REGISTRATION_WITH_API_ENDPOINT_ENABLED'] = 'true';
    }

    /**
     * Test register user when method is not POST
     *
     * @return void
     */
    public function testRegisterUserWhenMethodIsNotPost(): void
    {
        $this->client->request('GET', '/auth/register');

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertEquals('error', $responseData['status']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test register user when registration with API endpoint is disabled
     *
     * @return void
     */
    public function testRegisterUserWhenRegistrationWithApiEndpointIsDisabled(): void
    {
        // simulate registration with API endpoint is disabled
        $_ENV['REGISTRATION_WITH_API_ENDPOINT_ENABLED'] = 'false';

        // simulate request to register endpoint
        $this->client->request('POST', '/auth/register');

        // get response content
        $responseContent = $this->client->getResponse()->getContent();

        // check if response content is empty
        if (!$responseContent) {
            $this->fail('Response content is empty');
        }

        /** @var array<string> $responseData */
        $responseData = json_decode($responseContent, true);

        // assert response
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Registration with API endpoint is disabled!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when email is blank
     *
     * @return void
     */
    public function testRegisterUserWhenEmailIsBlank(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => '',
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('email value should not be blank.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when email is not valid
     *
     * @return void
     */
    public function testRegisterUserWhenEmailIsNotValid(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test',
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('email value is not a valid email address.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when first name is blank
     *
     * @return void
     */
    public function testRegisterUserWhenFirstNameIsBlank(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'firstName' => '',
            'lastName' => 'User',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('firstName value should not be blank., firstName value should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when first name is too short
     *
     * @return void
     */
    public function testRegisterUserWhenFirstNameIsTooShort(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'firstName' => 'T',
            'lastName' => 'User',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('firstName value should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when first name is too long
     *
     * @return void
     */
    public function testRegisterUserWhenFirstNameIsTooLong(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@test.com',
            'firstName' => str_repeat('a', 100),
            'lastName' => 'Test',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('firstName value should have at most 80 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when last name is blank
     *
     * @return void
     */
    public function testRegisterUserWhenLastNameIsBlank(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@test.com',
            'firstName' => 'Test',
            'lastName' => '',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('lastName value should not be blank., lastName value should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when last name is too short
     *
     * @return void
     */
    public function testRegisterUserWhenLastNameIsTooShort(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@test.com',
            'firstName' => 'Test',
            'lastName' => 'T',
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('lastName value should have at least 2 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when last name is too long
     *
     * @return void
     */
    public function testRegisterUserWhenLastNameIsTooLong(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@test.com',
            'firstName' => 'Test',
            'lastName' => str_repeat('a', 100),
            'password' => 'test123',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('lastName value should have at most 80 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when password is blank
     *
     * @return void
     */
    public function testRegisterUserWhenPasswordIsBlank(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@test.com',
            'firstName' => 'Test',
            'lastName' => 'Test',
            'password' => '',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('password value should not be blank., password value should have at least 6 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when password is too short
     *
     * @return void
     */
    public function testRegisterUserWhenPasswordIsTooShort(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 't',
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('password value should have at least 6 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when password is too long
     *
     * @return void
     */
    public function testRegisterUserWhenPasswordIsTooLong(): void
    {
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => str_repeat('a', 129),
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
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('password value should have at most 128 characters.', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Test register user when successful
     *
     * @return void
     */
    public function testRegisterUserWhenSuccessful(): void
    {
        // generate testing email
        $faker = Factory::create();
        $email = $faker->email();

        // simulate request to register endpoint
        $this->client->request('POST', '/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'firstName' => 'Test',
            'lastName' => 'Test',
            'password' => 'test123',
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
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('User: ' . $email . ' created successfully!', $responseData['message']);
        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }
}
