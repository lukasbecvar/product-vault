<?php

namespace App\Tests\Middleware;

use Exception;
use App\Manager\ErrorManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use App\Middleware\DatabaseOnlineMiddleware;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DatabaseOnlineMiddlewareTest
 *
 * Test the database online middleware
 *
 * @package App\Tests\Middleware
 */
class DatabaseOnlineMiddlewareTest extends TestCase
{
    private DatabaseOnlineMiddleware $middleware;
    private Connection & MockObject $connectionMock;
    private ErrorManager & MockObject $errorManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->connectionMock = $this->createMock(Connection::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create the middleware instance
        $this->middleware = new DatabaseOnlineMiddleware(
            $this->connectionMock,
            $this->errorManagerMock
        );
    }

    /**
     * Test if the database connection is successful
     *
     * @return void
     */
    public function testRequestWithSuccessfulDatabaseConnection(): void
    {
        // mock the error manager
        $this->errorManagerMock->expects($this->never())->method('handleError');

        // execute the middleware
        $this->middleware->onKernelRequest();
    }

    /**
     * Test if the database offline error is handled
     *
     * @return void
     */
    public function testRequestWithFailedDatabaseConnection(): void
    {
        // mock the database connection
        $this->connectionMock->expects($this->once())
            ->method('executeQuery')->willThrowException(new Exception('Database connection failed'));

        // expect handle error method call
        $this->errorManagerMock->expects($this->once())->method('handleError');

        // execute the middleware
        $this->middleware->onKernelRequest();
    }
}
