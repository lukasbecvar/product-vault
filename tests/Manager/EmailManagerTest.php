<?php

namespace App\Tests\Manager;

use App\Manager\EmailManager;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Class EmailManagerTest
 *
 * Test cases for email manager
 *
 * @package App\Tests\Manager
 */
class EmailManagerTest extends TestCase
{
    private EmailManager $emailManager;
    private MailerInterface & MockObject $mailerMock;
    private ErrorManager & MockObject $errorManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create email manager instance
        $this->emailManager = new EmailManager($this->mailerMock, $this->errorManagerMock);
    }

    /**
     * Test send email
     *
     * @return void
     */
    public function testSendEmail(): void
    {
        $_ENV['MAILER_ENABLED'] = 'true';

        // mock email sending
        $this->mailerMock->expects($this->once())->method('send');

        // call tested method
        $this->emailManager->sendEmail('test@test.com', 'Test subject', ['test' => 'test'], 'default');
    }
}
