<?php

namespace App\Tests\Manager;

use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ErrorManagerTest
 *
 * Test cases for error manager
 *
 * @package App\Tests\Manager
 */
class ErrorManagerTest extends TestCase
{
    private ErrorManager $errorManager;

    protected function setUp(): void
    {
        // create error manager instance
        $this->errorManager = new ErrorManager();
    }

    /**
     * Test handle error exception
     *
     * @return void
     */
    public function testHandleError(): void
    {
        // expect http exception to be thrown
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Page not found');
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // call tested method
        $this->errorManager->handleError('Page not found', Response::HTTP_NOT_FOUND);
    }
}
