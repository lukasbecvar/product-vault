<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use App\Util\JsonUtil;
use App\Manager\ErrorManager;
use App\Manager\CacheManager;
use PHPUnit\Framework\TestCase;
use App\Util\CurrencyConvertorUtil;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CurrencyConvertorUtilTest
 *
 * Test cases for currency convertor util
 *
 * @package App\Tests\Util
 */
class CurrencyConvertorUtilTest extends TestCase
{
    private MockObject & AppUtil $appUtil;
    private MockObject & JsonUtil $jsonUtil;
    private MockObject & ErrorManager $errorManager;
    private MockObject & CacheManager $cacheManager;
    private CurrencyConvertorUtil $currencyConvertorUtil;

    protected function setUp(): void
    {
        // mock dependencies
        $this->appUtil = $this->createMock(AppUtil::class);
        $this->jsonUtil = $this->createMock(JsonUtil::class);
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->cacheManager = $this->createMock(CacheManager::class);

        // create currency convertor util instance
        $this->currencyConvertorUtil = new CurrencyConvertorUtil(
            $this->appUtil,
            $this->jsonUtil,
            $this->errorManager,
            $this->cacheManager
        );
    }

    /**
     * Test convert currency
     *
     * @return void
     */
    public function testConvertCurrency(): void
    {
        // mock getExchangeRate to return a valid response
        $exchangeRateData = ['rates' => ['USD' => 1.1]]; // Example: fromCurrency to USD

        // mock cache data
        $this->cacheManager->method('checkIsCacheValueExists')->willReturn(true);
        $this->cacheManager->method('getCacheValue')->willReturn(json_encode($exchangeRateData));

        // call tested method
        $result = $this->currencyConvertorUtil->convertCurrency('EUR', 100, 'USD');

        // assert result
        $this->assertEquals(110.0, $result);
    }

    /**
     * Test get exchange rate from cache
     *
     * @return void
     */
    public function testGetExchangeRateFromCache(): void
    {
        // mock cache manager
        $exchangeRateData = ['rates' => ['USD' => 1.1]];

        // mock cache data
        $this->cacheManager->method('checkIsCacheValueExists')->willReturn(true);
        $this->cacheManager->method('getCacheValue')->willReturn(json_encode($exchangeRateData));

        // call tested method
        $result = $this->currencyConvertorUtil->getExchangeRate('EUR');

        // assert result
        $this->assertEquals($exchangeRateData, $result);
    }

    /**
     * Test get exchange rate from API when not in cache
     *
     * @return void
     */
    public function testGetExchangeRateNotInCache(): void
    {
        // simulate not data found in cache
        $this->cacheManager->method('checkIsCacheValueExists')->willReturn(false);

        // mock api response
        $apiResponse = ['result' => 'success', 'rates' => ['USD' => 1.1]];
        $this->appUtil->method('getEnvValue')->willReturn($_ENV['EXCHANGE_RATE_API_ENDPOINT']);
        $this->jsonUtil->method('getJson')->willReturn($apiResponse);

        // expect save data to cache
        $this->cacheManager->expects($this->once())->method('saveCacheValue')->with(
            $this->stringContains('exchange_rate_EUR'),
            $this->stringContains('rates')
        );

        // call tested method
        $result = $this->currencyConvertorUtil->getExchangeRate('EUR');

        // assert result
        $this->assertEquals($apiResponse, $result);
    }
}
