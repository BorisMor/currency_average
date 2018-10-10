<?php

namespace Lib;

abstract class ProviderExchange {

    /** @var string Валюта рубль */
    const CURRENCY_RUB = 'rub';
    /** @var string Валюта доллар */
    const CURRENCY_USD = 'usd';
    /** @var string Валюта евро */
    const CURRENCY_EUR = 'eur';

    /** @var int Проблема с первоначальной валютой */
    const ERROR_CURRENCY_FROM = 1001;
    /** @var int Проблема с конечной валютой */
    const ERROR_CURRENCY_TO = 1002;
    /** @var int Валюты идентичны */
    const ERROR_CURRENCY_IDENTICAL = 1003;
    /** @var int Невалидный код валюты */
    const ERROR_CURRENCY_VALID = 1004;

    /** @var \DateTime Дата на которую нужен курс */
    protected $_date;
    protected $_currencyFrom;
    protected $_currencyTo;

    /**
     * Значение для провайдера
     * @return float
     */
    abstract protected function getValueProvider(): float;

    /**
     * Индификактор данных
     * @return string
     */
    abstract public static function getId(): string;

    /**
     * Получить дату
     * @return \DateTime
     */
    public function getDate()
    {
        if (!empty($this->_date)) {
            return $this->_date;
        }

        return $this->_date = new \DateTime();
    }

    /**
     * Установить дату
     * @param \DateTime $value
     * @return $this
     */
    public function setDate(\DateTime $value)
    {
        $this->_date = $value;
        return $this;
    }

    /**
     * Установить валюту
     * @param $from
     * @param $to
     * @return $this
     */
    public function setCurrency($from, $to)
    {
        $this->_currencyFrom = $from;
        $this->_currencyTo = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyFrom()
    {
        return $this->_currencyFrom;
    }

    /**
     * @return string
     */
    public function getCurrencyTo()
    {
        return $this->_currencyTo;
    }


    /**
     * Валидация данных
     * @throws \Exception
     */
    public function validate()
    {
        if (empty($this->_currencyFrom)) {
            throw new \Exception('Не установлена начальная валюта', self::ERROR_CURRENCY_FROM);
        }

        if (empty($this->_currencyTo)) {
            throw new \Exception('Не установлена конечная валюта', self::ERROR_CURRENCY_TO);
        }

        if ($this->_currencyTo === $this->_currencyFrom) {
            throw new \Exception('Валюты идентичны', self::ERROR_CURRENCY_IDENTICAL);
        }
    }


    function getValue():float {
        $this->validate();
        return $this->getValueProvider();
    }
}
