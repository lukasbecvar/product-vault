<?php

namespace App\Tests\Manager;

use Exception;
use App\Util\AppUtil;
use App\Util\SecurityUtil;
use App\Manager\LogManager;
use App\Manager\ErrorManager;
use App\Util\VisitorInfoUtil;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LogManagerTest
 *
 * Test cases for log manager functionality
 *
 * @package App\Test\Manager
 */
class LogManagerTest extends TestCase
{
    private LogManager $logManager;
    private AppUtil & MockObject $appUtilMock;
    private SecurityUtil & MockObject $securityUtilMock;
    private ErrorManager & MockObject $errorManagerMock;
    private VisitorInfoUtil & MockObject $visitorInfoUtilMock;
    private EntityManagerInterface & MockObject $entityManagerMock;

    protected function setUp(): void
    {
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->securityUtilMock = $this->createMock(SecurityUtil::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);
        $this->visitorInfoUtilMock = $this->createMock(VisitorInfoUtil::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // create log manager instance
        $this->logManager = new LogManager(
            $this->appUtilMock,
            $this->securityUtilMock,
            $this->errorManagerMock,
            $this->visitorInfoUtilMock,
            $this->entityManagerMock
        );
    }

    /**
     * Tets save log to database when logging is enabled
     *
     * @return void
     */
    public function testSaveLogWhenLoggingIsDisabled(): void
    {
        // mock database logging to be disabled
        $this->appUtilMock->method('isDatabaseLoggingEnabled')->willReturn(false);

        // expect that persist and flush will not be called
        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        // call tested method
        $this->logManager->saveLog('Test', 'Test message');
    }

    /**
     * Tests save log to database when log level is too low
     *
     * @return void
     */
    public function testSaveLogWhenLogLevelIsTooLow(): void
    {
        // mock log manager config
        $this->appUtilMock->method('isDatabaseLoggingEnabled')->willReturn(true);
        $this->appUtilMock->method('getEnvValue')->with('LOG_LEVEL')->willReturn('2'); // LEVEL_WARNING (2)

        // expect that persist and flush will not be called
        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        // call tested method
        $this->logManager->saveLog('Test', 'Test message', LogManager::LEVEL_INFO); // LEVEL_INFO
    }

    /**
     * Tests save log to database with success result
     *
     * @return void
     */
    public function testSaveLogWithSuccess(): void
    {
        // mock log manager config
        $this->appUtilMock->method('isDatabaseLoggingEnabled')->willReturn(true);
        $this->appUtilMock->method('getEnvValue')->with('LOG_LEVEL')->willReturn('4'); // LEVEL_INFO

        // mock escape string method
        $this->securityUtilMock->method('escapeString')->willReturnCallback(function ($string) {
            return $string;
        });

        // mock visitor info
        $this->visitorInfoUtilMock->method('getUserAgent')->willReturn('TestAgent');
        $this->visitorInfoUtilMock->method('getIP')->willReturn('127.0.0.1');

        // mock request info
        $this->appUtilMock->method('getRequestUri')->willReturn('/test-uri');
        $this->appUtilMock->method('getRequestMethod')->willReturn('POST');

        // expect that persist and flush will be called once
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        // call tested method
        $this->logManager->saveLog('Test', 'Test message');
    }

    /**
     * Tests save log to database with exception
     *
     * @return void
     */
    public function testSaveLogThrowsException(): void
    {
        // mock log manager config
        $this->appUtilMock->method('isDatabaseLoggingEnabled')->willReturn(true);
        $this->appUtilMock->method('getEnvValue')->with('LOG_LEVEL')->willReturn('4'); // LEVEL_INFO

        // mock escape string method
        $this->securityUtilMock->method('escapeString')->willReturnCallback(function ($string) {
            return $string;
        });

        // mock visitor info
        $this->visitorInfoUtilMock->method('getUserAgent')->willReturn('TestAgent');
        $this->visitorInfoUtilMock->method('getIP')->willReturn('127.0.0.1');

        // mock exception during persist
        $this->entityManagerMock->method('persist')->willThrowException(new Exception('Database error'));

        // expect error handler call
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            $this->equalTo('error to save log: Database error'),
            $this->equalTo(JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
        );

        // call tested method
        $this->logManager->saveLog('Test', 'Test message');
    }
}
