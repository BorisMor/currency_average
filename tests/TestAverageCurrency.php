<?php

namespace test;

use Lib\AverageCurrency;
use Lib\ProviderExchange;
use Lib\ProviderExchangeCBR;
use Lib\ProviderExchangeRBC;
use PHPUnit\Framework\TestCase;

require './vendor/autoload.php';

class TestAverageCurrency extends TestCase
{

    /**
     * Параметры AverageCurrency
     * @param string $from начальная валюта
     * @param string $to конечная валюта
     * @param int $errorCod Код ошибки
     * @throws \Exception
     * @dataProvider providerParamsAverageCurrency
     */
    public function testParamsAverageCurrency($from, $to, $errorCod)
    {
        try {
            $model = AverageCurrency::model($from, $to);
            $model->getValue();
        } catch (\Exception $e) {
            $isNormCase = ($e->getCode() === $errorCod);
            $this->assertTrue($isNormCase);

            // Не предусмотрена ошибка
            if (!$isNormCase) {
                throw $e;
            }

            return;
        }

        $this->assertTrue($errorCod === null);
    }

    public function providerParamsAverageCurrency()
    {
        /**
         * Параметры:
         * [ начальная валюта , конечная валюта , код ошибки ]
         * Если ошибки не должно быть то код ошибки null
         */
        return [
            'Норма USD' => [ProviderExchange::CURRENCY_USD, ProviderExchange::CURRENCY_RUB, null],
            'Норма EUR' => [ProviderExchange::CURRENCY_EUR, ProviderExchange::CURRENCY_RUB, null],
            'Не указали валюту #1' => [null, ProviderExchange::CURRENCY_RUB, ProviderExchange::ERROR_CURRENCY_FROM],
            'Не указали валюту #2' => [ProviderExchange::CURRENCY_RUB, null, ProviderExchange::ERROR_CURRENCY_TO],
            'Невалидный код валюты' => ['xxx', 'yyy', ProviderExchange::ERROR_CURRENCY_VALID],
            'Идентичные валюты' => [ProviderExchange::CURRENCY_RUB, ProviderExchange::CURRENCY_RUB, ProviderExchange::ERROR_CURRENCY_IDENTICAL]
        ];
    }

    /**
     * Подсчет среднего значения
     * @param array $data
     * @return float|int
     */
    private function getAverage(array $data)
    {
        $sum = 0;
        foreach ($data as $val) {
            $sum += $val;
        }

        return $sum / count($data);

    }


    /**
     * Сравнение данных из класса и тестовых данных
     * @param AverageCurrency $model Объект для подсчета среднего
     * @param array $dataTest Тестовый набор данных
     * @throws \Exception
     */
    private function checkData(AverageCurrency $model, array $dataTest)
    {
        /** @var array $dataSource Курсы разбитые по источникам */
        $dataSource = $model->getValuesBySource();

        foreach ($dataSource as $key => $value) {
            $testValue = $dataTest[$key] ?? null;
            $this->assertTrue($testValue === $value, "Расхождение по $key ($testValue !== $value)");
        }

        $averageTest = $this->getAverage($dataTest);
        $averageObject = $this->getAverage($dataSource);

        $this->assertTrue($averageTest === $averageObject, "Расхождение по расчетному среднему");
        $this->assertTrue($averageTest === $model->getValue(), "Расхождение по среднему из модели");
    }

    /**
     * Проверка курсов для доллара
     *
     * @param string $date На дату
     * @param array $dataTest Тестовый набор данных
     * @throws \Exception
     * @dataProvider providerDataUsd
     */
    public function testDataUsd($date, array $dataTest)
    {
        $model = AverageCurrency::model(ProviderExchange::CURRENCY_USD, ProviderExchange::CURRENCY_RUB)->setDate($date);
        $this->checkData($model, $dataTest);
    }

    /**
     * Курсы доллара для разных дат
     * @return array
     */
    public function providerDataUsd()
    {
        $idCBR = ProviderExchangeCBR::getId();
        $idRBC = ProviderExchangeRBC::getId();

        return [
            'USD 2015-01-05' => ['2015-01-05', [
                $idCBR => 56.2376,
                $idRBC => 56.2376
            ]],
            'USD 2016-01-05' => ['2016-01-05', [
                $idCBR => 72.9299,
                $idRBC => 72.9299
            ]],
            'USD 2017-01-05' => ['2017-01-05', [
                $idCBR => 60.6569,
                $idRBC => 60.6569
            ]],
            'USD 2018-01-05' => ['2018-01-05', [
                $idCBR => 57.6002,
                $idRBC => 57.6002
            ]],
        ];
    }

    /**
     * Проверка курсов для доллара
     *
     * @param string $date На дату
     * @param array $dataTest Тестовый набор данных
     * @throws \Exception
     * @dataProvider providerDataEUR
     */
    public function testDataEur($date, array $dataTest)
    {
        $model = AverageCurrency::model(ProviderExchange::CURRENCY_EUR, ProviderExchange::CURRENCY_RUB)->setDate($date);
        $this->checkData($model, $dataTest);
    }


    /**
     * Курсы евро для разных дат
     * @return array
     */
    public function providerDataEUR()
    {
        $idCBR = ProviderExchangeCBR::getId();
        $idRBC = ProviderExchangeRBC::getId();

        return [
            'EUR 2015-01-05' => ['2015-01-05', [
                $idCBR => 68.3681,
                $idRBC => 68.3681
            ]],
            'EUR 2016-01-05' => ['2016-01-05', [
                $idCBR => 79.6395,
                $idRBC => 79.6395
            ]],
            'EUR 2017-01-05' => ['2017-01-05', [
                $idCBR => 63.8111,
                $idRBC => 63.8111
            ]],
            'EUR 2018-01-05' => ['2018-01-05', [
                $idCBR => 68.8668,
                $idRBC => 68.8668
            ]],
        ];
    }
}

?>
