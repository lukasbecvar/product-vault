<?php

namespace App\Tests\Manager;

use App\Entity\User;
use Monolog\Test\TestCase;
use App\Manager\LogManager;
use App\Manager\UserManager;
use App\Util\VisitorInfoUtil;
use App\Manager\ErrorManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UserManagerTest
 *
 * Test cases for user manager
 *
 * @package App\Tests\Manager
 */
class UserManagerTest extends TestCase
{
    private UserManager $userManager;
    private LogManager & MockObject $logManagerMock;
    private ErrorManager & MockObject $errorManagerMock;
    private UserRepository & MockObject $userRepositoryMock;
    private VisitorInfoUtil & MockObject $visitorInfoUtilMock;
    private EntityManagerInterface & MockObject $entityManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManagerMock = $this->createMock(LogManager::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->visitorInfoUtilMock = $this->createMock(VisitorInfoUtil::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // create user manager instance
        $this->userManager = new UserManager(
            $this->logManagerMock,
            $this->errorManagerMock,
            $this->userRepositoryMock,
            $this->visitorInfoUtilMock,
            $this->entityManagerMock
        );
    }

    /**
     * Test is user exists
     *
     * @return void
     */
    public function testIsUserExists(): void
    {
        // call tested method
        $isUserExists = $this->userManager->isUserExists('test@test.test');

        // assert result
        $this->assertIsBool($isUserExists);
    }

    /**
     * Test register user
     *
     * @return void
     */
    public function testRegisterUser(): void
    {
        // testing user data
        $email = 'test@test.com';
        $firstName = 'John';
        $lastName = 'Doe';
        $password = 'secure_password';
        $ipAddress = '127.0.0.1';
        $userAgent = 'TestAgent';

        // mock repository to simulate no existing user
        $this->userRepositoryMock->expects($this->once())->method('findByEmail')
            ->with($email)->willReturn(null);

        // mock get visitor info
        $this->visitorInfoUtilMock->expects($this->once())->method('getIP')
            ->willReturn($ipAddress);
        $this->visitorInfoUtilMock->expects($this->once())->method('getUserAgent')
            ->willReturn($userAgent);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(User::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'new user registered: ' . $email,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->registerUser($email, $firstName, $lastName, $password);
    }

    /**
     * Test check if user has role
     *
     * @return void
     */
    public function testCheckIfUserHasRole(): void
    {
        // testing user data
        $email = 'test@test.com';
        $role = 'ROLE_ADMIN';

        // mock repository to simulate existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getRoles')->willReturn([$role]);
        $this->userRepositoryMock->expects($this->once())->method('findByEmail')
            ->with($email)->willReturn($user);

        // call tested method
        $this->assertTrue($this->userManager->checkIfUserHasRole($email, $role));
    }

    /**
     * Test add role to user
     *
     * @return void
     */
    public function testAddRoleToUser(): void
    {
        $email = 'test@test.com';
        $role = 'ROLE_ADMIN';

        // mock existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getRoles')->willReturn([]);
        $user->expects($this->once())->method('addRole')->with($role);

        // mock repository to return the user twice
        $this->userRepositoryMock->expects($this->exactly(2))->method('findByEmail')->with($email)
            ->willReturn($user);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('persist')->with($user);
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user role added: ' . $email . ' - ' . $role,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->addRoleToUser($email, $role);
    }

    /**
     * Test remove role from user
     *
     * @return void
     */
    public function testRemoveRoleFromUser(): void
    {
        $email = 'test@test.com';
        $role = 'ROLE_ADMIN';

        // mock existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getRoles')->willReturn([$role]);
        $user->expects($this->once())->method('removeRole')->with($role);

        // mock repository to return the user twice
        $this->userRepositoryMock->expects($this->exactly(2))->method('findByEmail')->with($email)
            ->willReturn($user);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('persist')->with($user);
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user role removed: ' . $email . ' - ' . $role,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->removeRoleFromUser($email, $role);
    }
}
