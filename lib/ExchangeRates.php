<?php
namespace Lib;

use phpDocumentor\Reflection\Types\This;

/**
 * Курс валюты
 * Class ExchangeRates
 * @package Lib
 */
class ExchangeRates{

    /** @var ProviderExchange Провайдер данных */
    private $_provider;

    /** @var float Курс */
    private $_value;

    /**
     * ExchangeRates constructor.
     * @param ProviderExchange $provider Источник данных
     * @param string $fromCurrency Исходная валюта
     * @param string $toCurrency Получаемая валюта
     * @throws \Exception
     */
    public function __construct(ProviderExchange $provider, $fromCurrency, $toCurrency)
    {
        $this->_provider = clone $provider;
        $this->setDate(new \DateTime());
        $this->_provider->setCurrency($fromCurrency, $toCurrency);
        $this->_provider->validate();
    }

    /**
     * Установить дату курса
     * @param \DateTime|string $value
     * @return $this
     * @throws \Exception
     */
    public function setDate($value)
    {
        $this->_value = null;
        $this->_provider->setDate(self::isDate($value));
        return $this;
    }

    /**
     * Вернет курс
     * @return float
     * @throws \Exception
     */
    public function getValue()
    {
        if ($this->_value) {
            return $this->_value;
        }

        $this->_provider->validate();
        return $this->_provider->getValue();
    }

    /**
     * Преобразуем строку в DateTime
     * @param string|\DateTime $value
     * @return \DateTime
     * @throws \Exception
     */
    public static function isDate($value = 'now')
    {
        if (is_object($value) && $value instanceof \DateTime) {
            return $value;
        }

        if (!is_string($value) || empty($value)) {
            throw new \Exception('Неверный формат даты: ' . $value);
        }

        $value = trim($value);
        if($value === 'now') {
            return new \DateTime();
        }

        $result = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($result == false) {
            $result = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $value);
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('Y-m-d H:i:s.u', $value);
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('Y-m-d', $value);
            if ($result !== false) {
                $result->setTime(0, 0, 0);
            }
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d-m-Y', $value);
            if ($result !== false) {
                $result->setTime(0, 0, 0);
            }
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d.m.Y H:i:s', $value);
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d.m.Y', $value);
            if ($result !== false) {
                $result->setTime(0, 0, 0);
            }
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d M Y', $value);
            if ($result !== false) {
                $result->setTime(0, 0, 0);
            }
        }
        if($result == false){
            throw new \Exception('Не смогли преобразовать в дату: ' . $value);
        }
        return $result;
    }
}
