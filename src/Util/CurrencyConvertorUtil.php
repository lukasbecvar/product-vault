<?php

namespace App\Util;

use Exception;
use App\Manager\CacheManager;
use App\Manager\ErrorManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CurrencyConvertorUtil
 *
 * Util for price currency conversion
 *
 * @package App\Util
 */
class CurrencyConvertorUtil
{
    private AppUtil $appUtil;
    private JsonUtil $jsonUtil;
    private ErrorManager $errorManager;
    private CacheManager $cacheManager;

    public function __construct(
        AppUtil $appUtil,
        JsonUtil $jsonUtil,
        ErrorManager $errorManager,
        CacheManager $cacheManager
    ) {
        $this->appUtil = $appUtil;
        $this->jsonUtil = $jsonUtil;
        $this->errorManager = $errorManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Convert currency
     *
     * @param string $fromCurrency The currency to convert from
     * @param float $amount The amount to convert
     * @param string $toCurrency The currency to convert to
     *
     * @return float The converted amount
     */
    public function convertCurrency(string $fromCurrency, float $amount, string $toCurrency): float
    {
        // get exchange rate data
        $data = $this->getExchangeRate($fromCurrency);

        // check if target currency is found in the rates
        if (!isset($data['rates'][$toCurrency])) {
            $this->errorManager->handleError(
                message: 'Currency: ' . $toCurrency . ' not found in exchange rates',
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // get rate
        $rate = $data['rates'][$toCurrency];

        // format result
        return round($amount * $rate, 2);
    }

    /**
     * Get exchange rate from api
     *
     * @param string $fromCurrency The currency to get exchange rate from
     *
     * @return array<mixed> The exchange rate data
     */
    public function getExchangeRate(string $fromCurrency): array
    {
        // check if data found in cache
        if ($this->cacheManager->checkIsCacheValueExists('exchange_rate_' . $fromCurrency)) {
            $data = (string) $this->cacheManager->getCacheValue('exchange_rate_' . $fromCurrency);

            // decode json data
            $data = json_decode($data, true);

            // return json data
            return $data;
        }

        // get api endpoint url from .env
        $apiUrl = $this->appUtil->getEnvValue('EXCHANGE_RATE_API_ENDPOINT') . '/' . $fromCurrency;
        $cacheExpirationTTL = (int) $this->appUtil->getEnvValue('EXCHAENGE_DATA_CACHE_TTL');

        try {
            // get exchange rates from api
            $data = $this->jsonUtil->getJson($apiUrl);

            // check if result is success
            if ($data !== null && isset($data['result']) && $data['result'] !== 'success') {
                $this->errorManager->handleError(
                    message: 'Error fetching exchange rates because result is not success',
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    exceptionMessage: 'Error type: ' . ($data['error-type'] ?? 'unknown')
                );
            }

            // encode json data
            $dataToCache = json_encode($data);

            // check if data is empty
            if ($dataToCache == false || $data == null) {
                $this->errorManager->handleError(
                    message: 'Error to get exchange rates',
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    exceptionMessage: 'Error fetching exchange rates'
                );
            }

            // save data to cache
            $this->cacheManager->saveCacheValue('exchange_rate_' . $fromCurrency, $dataToCache, $cacheExpirationTTL);

            // return json data
            return $data;
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error to get exchange rates',
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }
}
