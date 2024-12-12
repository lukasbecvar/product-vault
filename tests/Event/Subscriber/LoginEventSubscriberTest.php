<?php

namespace App\Tests\Event\Subscriber;

use App\Manager\LogManager;
use App\Manager\UserManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use App\Event\Subscriber\LoginEventSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class LoginEventSubscriberTest
 *
 * Test cases for login event subscriber
 *
 * @package App\Tests\Event\Subscriber
 */
class LoginEventSubscriberTest extends TestCase
{
    private LogManager & MockObject $logManager;
    private UserManager & MockObject $userManager;
    private RequestStack & MockObject $requestStack;
    private LoginEventSubscriber $loginEventSubscriber;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->userManager = $this->createMock(UserManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        // init login event subscriber
        $this->loginEventSubscriber = new LoginEventSubscriber(
            $this->logManager,
            $this->userManager,
            $this->requestStack
        );
    }

    /**
     * Test get subscribed events
     *
     * @return void
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertArrayHasKey(
            AuthenticationSuccessEvent::class,
            LoginEventSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Test handle successful login
     *
     * @return void
     */
    public function testOnSecurityAuthenticationSuccess(): void
    {
        // create request
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/auth/login']);
        $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        // mock user object
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())->method('getUserIdentifier')->willReturn('testuser');
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        // mock request event
        $event = new AuthenticationSuccessEvent($token);

        // expect update user data call
        $this->userManager->expects($this->once())->method('updateUserDataOnLogin')->with('testuser');

        // expect save log call
        $this->logManager->expects($this->once())->method('saveLog')->with(
            'authentication',
            'user: testuser successfully authenticated',
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->loginEventSubscriber->onSecurityAuthenticationSuccess($event);
    }

    /**
     * Test handle login with invalid request
     *
     * @return void
     */
    public function testOnSecurityAuthenticationSuccessWithInvalidRequest(): void
    {
        // create request
        $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn(null);

        // mock event
        $token = $this->createMock(TokenInterface::class);
        $event = new AuthenticationSuccessEvent($token);

        // expect update user data not to be called
        $this->userManager->expects($this->never())->method('updateUserDataOnLogin');

        // expect save log not to be called
        $this->logManager->expects($this->never())->method('saveLog');

        // call tested method
        $this->loginEventSubscriber->onSecurityAuthenticationSuccess($event);
    }
}
