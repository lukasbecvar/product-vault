<?php

namespace App\Middleware;

use Exception;
use App\Manager\ErrorManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatabaseOnlineMiddleware
 *
 * Middleware for checking the database connection
 *
 * @package App\Middleware
 */
class DatabaseOnlineMiddleware
{
    private Connection $connection;
    private ErrorManager $errorManager;

    public function __construct(Connection $connection, ErrorManager $errorManager)
    {
        $this->connection = $connection;
        $this->errorManager = $errorManager;
    }

    /**
     * Check database connection status
     *
     * @throws Exception Database connection error
     *
     * @return void
     */
    public function onKernelRequest(): void
    {
        try {
            // select for connection try
            $this->connection->executeQuery('SELECT 1');
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'database connection error',
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
