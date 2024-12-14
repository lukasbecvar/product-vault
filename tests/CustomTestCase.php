<?php

namespace App\Tests;

use DateTime;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Class CustomTestCase
 *
 * Custom test case for extending web test case
 *
 * @package App\Tests
 */
class CustomTestCase extends WebTestCase
{
    /**
     * Generate user token for testing purposes
     *
     * @return string The generated JWT token
     */
    public function generateJwtToken(): string
    {
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // create test user
        $fakeUser = new User();
        $fakeUser->setEmail('test@test.test')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setRoles(['ROLE_USER'])
            ->setRegisterTime(new DateTime())
            ->setLastLoginTime(new DateTime())
            ->setIpAddress('127.0.0.1')
            ->setUserAgent('TestAgent')
            ->setStatus('active');

        // hash password
        $password = $passwordHasher->hashPassword($fakeUser, 'test');
        $fakeUser->setPassword($password);

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);

        // generate JWT token
        return $jwtManager->create($fakeUser);
    }
}
