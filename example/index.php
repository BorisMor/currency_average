<?php

namespace Example;

use Lib\AverageCurrency;
use Lib\ProviderExchange;

require_once('../vendor/autoload.php');

$date = new \DateTime();
$average = new AverageCurrency($date, ProviderExchange::CURRENCY_USD, ProviderExchange::CURRENCY_RUB);
echo "USD: {$average->getValue()}<br>";

$average = new AverageCurrency($date, ProviderExchange::CURRENCY_EUR, ProviderExchange::CURRENCY_RUB);
echo "EUR: {$average->getValue()}<br>";
