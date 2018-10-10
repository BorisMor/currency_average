<?php

namespace Lib;

use GuzzleHttp\Client;

/**
 * Курс валют от РБК
 * Class ProviderExchangeRBC
 * @package Lib
 */
class ProviderExchangeRBC extends ProviderExchange {

    /**
     * Получить код РБК для валюты
     * @param $value
     * @return string
     * @throws \Exception
     */
    protected function getIdCurrencyRBK($value) {
        switch ($value) {
            case self::CURRENCY_USD:
                return 'USD';
            case self::CURRENCY_EUR:
                return 'EUR';
            case self::CURRENCY_RUB:
                return 'RUR';
            default:
                throw new \Exception('Нет РБК кода для валюты ' . $value, self::ERROR_CURRENCY_VALID);
        }
    }

    /**
     * Запрос данных с сервера
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request()
    {
        /** @var Client $client */
        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
            'headers' => [ 'Content-Type' => 'application/json' ]
        ]);

        $urlPath = 'https://cash.rbc.ru/cash/json/converter_currency_rate';
        $urlParam = [
            'source'=>'cbrf',
            'sum' => 1,
            'date' => $this->getDate()->format('Y-m-d'),
            'currency_from' => $this->getIdCurrencyRBK($this->getCurrencyFrom()),
            'currency_to' => $this->getIdCurrencyRBK($this->getCurrencyTo()),
        ];

        $response =  $client->request('GET', $urlPath, [
            'query' => $urlParam
        ]);

        if ($response->getStatusCode() !== 200) {
            $url = $urlPath . '?' . http_build_query($urlParam);
            throw new \Exception('Cервер РБК вернул код ' . $response->getStatusCode() . " ($url)");
        }

        $json = $response->getBody()->getContents();
        if (empty($json)) {
            throw new \Exception('Не смог получить данные c сервера РБК');
        }

        return json_decode($json, true);
    }

    /**
     * Значение для провайдера
     * @return float
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getValueProvider(): float
    {
        $res = $this->request();
        $str = $res['data']['sum_result'] ?? null;
        if (empty($str)) {
            throw new \Exception('Проблема с данными РБК');
        } else {
            return (float)$str;
        }
    }


    /**
     * Индификактор данных
     * @return string
     */
    public static function getId(): string
    {
        return 'rbc';
    }
}
