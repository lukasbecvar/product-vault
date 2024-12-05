<?php

namespace App\Manager;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ErrorManager
 *
 * The manager for error handling
 *
 * @package App\Manager
 */
class ErrorManager
{
    /**
     * Handle error exception
     *
     * @param string $message The error message
     * @param int $code The error code
     *
     * @return never Always throws error exception
     */
    public function handleError(string $message, int $code): void
    {
        throw new HttpException($code, $message, null, [], $code);
    }
}
