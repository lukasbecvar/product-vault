<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Entity\Log;
use App\Util\AppUtil;
use App\Util\SecurityUtil;
use App\Util\VisitorInfoUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LogManager
 *
 * The manager for log system functionality
 *
 * @package App\Manager
 */
class LogManager
{
    // log levels definitions
    public const LEVEL_CRITICAL = 1;
    public const LEVEL_WARNING = 2;
    public const LEVEL_NOTICE = 3;
    public const LEVEL_INFO = 4;

    private AppUtil $appUtil;
    private SecurityUtil $securityUtil;
    private ErrorManager $errorManager;
    private VisitorInfoUtil $visitorInfoUtil;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AppUtil $appUtil,
        SecurityUtil $securityUtil,
        ErrorManager $errorManager,
        VisitorInfoUtil $visitorInfoUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->appUtil = $appUtil;
        $this->securityUtil = $securityUtil;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->visitorInfoUtil = $visitorInfoUtil;
    }

    /**
     * Save log message to database
     *
     * @param string $name The log name
     * @param string $message The log message
     * @param int $level The log level
     *
     * @throws Exception Error to persist or flush log to database
     *
     * @return void
     */
    public function saveLog(string $name, string $message, int $level = self::LEVEL_INFO): void
    {
        // check if log can be saved
        if (str_contains($message, 'Connection refused')) {
            return;
        }

        // check if database logging is enabled
        if (!$this->appUtil->isDatabaseLoggingEnabled()) {
            return;
        }

        // check required log level
        if ($level > (int) $this->appUtil->getEnvValue('LOG_LEVEL')) {
            return;
        }

        // escape log message
        $name = $this->securityUtil->escapeString($name);
        $message = $this->securityUtil->escapeString($message);

        // check if name or message is null
        if ($name == null || $message == null) {
            $this->errorManager->handleError(
                message: 'error to get or escape log name or message',
                code: JsonResponse::HTTP_BAD_REQUEST,
            );
        }

        // get log time
        $time = new DateTime();

        // get request data
        $requestUri = $this->appUtil->getRequestUri() ?? 'Unknown';
        $requestMethod = $this->appUtil->getRequestMethod() ?? 'Unknown';

        // get user info of current user
        $userAgent = $this->visitorInfoUtil->getUserAgent() ?? 'Unknown';
        $userIp = $this->visitorInfoUtil->getIP() ?? 'Unknown';
        $userId = 0;

        // create log entity
        $log = new Log();
        $log->setName($name)
            ->setMessage($message)
            ->setTime($time)
            ->setLevel($level)
            ->setUserId($userId)
            ->setUserAgent($userAgent)
            ->setRequestUri($requestUri)
            ->setRequestMethod($requestMethod)
            ->setIpAddress($userIp)
            ->setStatus('open');

        try {
            // save log to database
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to save log: ' . $e->getMessage(),
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
