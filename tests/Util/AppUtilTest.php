<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AppUtilTest
 *
 * Test cases for app util
 *
 * @package App\Tests\Util
 */
class AppUtilTest extends TestCase
{
    private AppUtil $appUtil;
    private KernelInterface $kernelInterface;
    private RequestStack & MockObject $requestStack;

    protected function setUp(): void
    {
        // mock dependencies
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->kernelInterface = $this->createMock(KernelInterface::class);

        // create the app util instance
        $this->appUtil = new AppUtil($this->requestStack, $this->kernelInterface);
    }

    /**
     * Test get request uri
     *
     * @return void
     */
    public function testGetRequestUri(): void
    {
        // mock request uri
        $requestUri = 'http://localhost/test';
        $request = $this->createMock(Request::class);
        $request->method('getRequestUri')->willReturn($requestUri);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        // call tested method
        $result = $this->appUtil->getRequestUri();

        // check result
        $this->assertEquals($requestUri, $result);
    }

    /**
     * Test get request method
     *
     * @return void
     */
    public function testGetRequestMethod(): void
    {
        // mock request method
        $requestMethod = 'GET';
        $request = $this->createMock(Request::class);
        $request->method('getMethod')->willReturn($requestMethod);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        // call tested method
        $result = $this->appUtil->getRequestMethod();

        // check result
        $this->assertEquals($requestMethod, $result);
    }

    /**
     * Test get app root directory
     *
     * @return void
     */
    public function testGetAppRootDir(): void
    {
        // get all root dir
        $result = $this->appUtil->getAppRootDir();

        // assert result
        $this->assertIsString($result);
    }

    /**
     * Test get environment variable value
     *
     * @return void
     */
    public function testGetEnvValue(): void
    {
        $_ENV['TEST_KEY'] = 'test-value';
        $this->assertSame('test-value', $this->appUtil->getEnvValue('TEST_KEY'));
    }

    /**
     * Test loging enabled check
     *
     * @return void
     */
    public function testIsDatabaseLoggingEnabled(): void
    {
        $_ENV['DATABASE_LOGGING'] = 'true';
        $this->assertTrue($this->appUtil->isDatabaseLoggingEnabled());

        $_ENV['DATABASE_LOGGING'] = 'false';
        $this->assertFalse($this->appUtil->isDatabaseLoggingEnabled());
    }
}