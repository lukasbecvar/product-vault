<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Entity\User;
use App\Util\VisitorInfoUtil;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserManager
 *
 * The manager for user related functionality
 *
 * @package App\Manager
 */
class UserManager
{
    private LogManager $logManager;
    private ErrorManager $errorManager;
    private UserRepository $userRepository;
    private VisitorInfoUtil $visitorInfoUtil;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LogManager $logManager,
        ErrorManager $errorManager,
        UserRepository $userRepository,
        VisitorInfoUtil $visitorInfoUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->visitorInfoUtil = $visitorInfoUtil;
    }

    /**
     * Get user repository
     *
     * @return UserRepository The user repository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * Check if user already exists
     *
     * @param string $email The email address of the user
     *
     * @return bool True if user exists, false otherwise
     */
    public function isUserExists(string $email): bool
    {
        return $this->userRepository->findByEmail($email) !== null;
    }

    /**
     * Register new user to database
     *
     * @param string $email The email address of the user
     * @param string $firstName The first name of the user
     * @param string $lastName The last name of the user
     * @param string $password The password of the user
     *
     * @return void
     */
    public function registerUser(string $email, string $firstName, string $lastName, string $password): void
    {
        // check if user already exists
        if ($this->isUserExists($email)) {
            $this->errorManager->handleError(
                message: 'user already exists: ' . $email,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // get visitor info
        $ipAddress = $this->visitorInfoUtil->getIP();
        $userAgent = $this->visitorInfoUtil->getUserAgent();

        // check if user info is valid
        if ($ipAddress == null || $userAgent == null) {
            $this->errorManager->handleError(
                message: 'invalid user info: ip address or user agent is null',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // create user entity
        $user = new User();
        $user->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles(['ROLE_USER'])
            ->setPassword($password)
            ->setRegisterTime(new DateTime())
            ->setLastLoginTime(new DateTime())
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setStatus('active');

        // save user to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to register user',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'new user registered: ' . $email,
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Check if user has specific role
     *
     * @param string $email The email address of the user
     * @param string $role The role to check
     *
     * @return bool True if user has role, false otherwise
     */
    public function checkIfUserHasRole(string $email, string $role): bool
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->findByEmail($email);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found: ' . $email,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        return in_array($role, $user->getRoles());
    }

    /**
     * Add role to specific user
     *
     * @param string $email The email address of the user
     * @param string $role The role to add
     *
     * @return void
     */
    public function addRoleToUser(string $email, string $role): void
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->findByEmail($email);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found: ' . $email,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        if ($this->checkIfUserHasRole($email, $role)) {
            $this->errorManager->handleError(
                message: 'user already has role: ' . $role,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // add role to user
        $user->addRole($role);

        // save user to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to add role to user',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user role added: ' . $email . ' - ' . $role,
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Remove role from specific user
     *
     * @param string $email The email address of the user
     * @param string $role The role to remove
     *
     * @return void
     */
    public function removeRoleFromUser(string $email, string $role): void
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->findByEmail($email);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found: ' . $email,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        if (!$this->checkIfUserHasRole($email, $role)) {
            $this->errorManager->handleError(
                message: 'user does not have role: ' . $role,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // remove role from user
        $user->removeRole($role);

        // save user to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'user not found: ' . $email,
                code: JsonResponse::HTTP_NOT_FOUND,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user role removed: ' . $email . ' - ' . $role,
            level: LogManager::LEVEL_INFO,
        );
    }
}
