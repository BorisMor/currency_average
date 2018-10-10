<?php

namespace Lib;

use GuzzleHttp\Client;


/**
 * Курс валют от ЦБР
 * https://www.cbr.ru/development/SXML/
 *
 * Class ProviderExchangeCBR
 * @package Lib
 */
class ProviderExchangeCBR extends ProviderExchange {

    /**
     * Получить код ЦБР для валюты
     * @param $value
     * @return string
     * @throws \Exception
     */
    protected function getIdCurrencyCBR($value) {
        switch ($value) {
            case self::CURRENCY_USD:
                return 'R01235';
            case self::CURRENCY_EUR:
                return 'R01239';
            default:
                throw new \Exception('Нет ЦБР кода для валюты ' . $value, self::ERROR_CURRENCY_VALID);
        }
    }


    /**
     * Запрос данных с сервера
     * @return \SimpleXMLElement
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request()
    {
        /** @var Client $client */
        $client = new \GuzzleHttp\Client(['http_errors' => false]);

        $urlPath = 'http://www.cbr.ru/scripts/XML_daily.asp';
        $urlParam = [
            'date_req' => $this->getDate()->format('d/m/Y'),
        ];

        $response =  $client->request('GET', $urlPath, [
            'query' => $urlParam
        ]);

        if ($response->getStatusCode() !== 200) {
            $url = $urlPath . '?' . http_build_query($urlParam);
            throw new \Exception('Cервер ЦБР вернул код ' . $response->getStatusCode() . " ($url)");
        }

        $xml = $response->getBody()->getContents();
        if (empty($xml)) {
            throw new \Exception('Не смог получить данные с сервера ЦБР');
        }

        return simplexml_load_string($xml);
    }

    /**
     * Значение для провайдера
     * @return float
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getValueProvider(): float
    {

        $idCurrency = $this->getIdCurrencyCBR($this->getCurrencyFrom());
        $res = $this->request();

        /** @var \SimpleXMLElement $el */
        foreach ($res->Valute as $el) {
            $checkIdCurrency = (string)$el->attributes()->ID;
            if ($checkIdCurrency === $idCurrency) {
                $str = (string)$el->Value;
                $str = str_replace(',', '.', $str);
                return (float)$str;
            }
        }

        return 0;
    }

    /**
     * Индификактор данных
     * @return string
     */
    public static function getId(): string
    {
        return 'cbr';
    }
}
