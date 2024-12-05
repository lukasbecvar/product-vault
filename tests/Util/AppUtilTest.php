<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

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

    protected function setUp(): void
    {
        // mock dependencies
        $this->kernelInterface = $this->createMock(KernelInterface::class);

        // create the app util instance
        $this->appUtil = new AppUtil(
            $this->kernelInterface
        );
    }

    /**
     * Test get app version
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
}
