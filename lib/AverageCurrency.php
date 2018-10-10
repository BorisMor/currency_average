<?php

namespace Lib;

/**
 * Подсчет среднего курса по всем источникам
 * Class AverageCurrency
 * @package Lib
 */
class AverageCurrency
{

    /** @var array Курсы валют из разных источников */
    protected $_exchangeRates;

    /** @var float Расчетное значение */
    protected $_value;

    /**
     * Источник данных
     * @return array
     */
    protected function getListProvider()
    {
        return [
            new ProviderExchangeCBR(),
            new ProviderExchangeRBC()
        ];
    }

    public function __construct($date, $fromCurrency, $toCurrency)
    {
        $this->_exchangeRates = [];

        /** @var ProviderExchange $provider */
        foreach ($this->getListProvider() as $provider) {
            $model = new ExchangeRates($provider, $fromCurrency, $toCurrency);
            $model->setDate($date);

            $this->_exchangeRates[$provider::getId()] = $model;
        }

    }

    /**
     * Получить среднее значение из всех источников
     * @return float|int
     * @throws \Exception
     */
    public function getValue()
    {
        if (!empty($this->_value)) {
            return $this->_value;
        }

        $sum = 0;

        /** @var ExchangeRates $exchange */
        foreach ($this->_exchangeRates as $exchange) {
            $sum += $exchange->getValue();
        }

        return $this->_value = $sum / count($this->_exchangeRates);
    }

    /**
     * Изменить дату курса
     * @param $value
     * @return $this
     * @throws \Exception
     */
    public function setDate($value)
    {
        $this->_value = null;
        /**
         * @var  $key
         * @var ExchangeRates $model
         */
        foreach ($this->_exchangeRates as $key => $model) {
            $model->setDate($value);
        }

        return $this;
    }

    /**
     * Значения курсов из разных источников
     * @return array
     */
    public function getValuesBySource()
    {
        $result = [];
        foreach ($this->_exchangeRates as $key => $model) {
            $result[$key] = $model->getValue();
        }

        return $result;
    }

    /**
     * @param $fromCurrency
     * @param $toCurrency
     * @return AverageCurrency
     */
    public static function model($fromCurrency, $toCurrency = ProviderExchange::CURRENCY_RUB)
    {
        return new static('now', $fromCurrency, $toCurrency);
    }
}
