<?php

namespace App\Manager;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Class EmailManager
 *
 * Manager for email sending functionality
 *
 * @package App\Manager
 */
class EmailManager
{
    private MailerInterface $mailer;
    private ErrorManager $errorManager;

    public function __construct(MailerInterface $mailer, ErrorManager $errorManager)
    {
        $this->mailer = $mailer;
        $this->errorManager = $errorManager;
    }

    /**
     * Send email to recipient (formated in twig template)
     *
     * @param string $recipient Recipient email address
     * @param string $subject Email subject
     * @param array<mixed> $context Email context
     * @param string $template Email template
     *
     * @return void
     */
    public function sendEmail(string $recipient, string $subject, array $context, string $template = 'default'): void
    {
        // check if mailer is enabled
        if ($_ENV['MAILER_ENABLED'] == 'false') {
            return;
        }

        // build email template
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_USERNAME'])
            ->to($recipient)
            ->subject($subject)
            ->htmlTemplate('email/' . $template . '.twig')
            ->context($context);

        try {
            // send email
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->errorManager->handleError(
                message: 'Email sending error',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
