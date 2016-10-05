<?php

// подключаем классы
require_once (__DIR__ . '/Google/Base.php');
require_once (__DIR__ . '/Google/Analytics.php');

// подключаемся к Google Analytics API
$analyticsObject = new \c3037\Google\Analytics("google_api_keys.json");

// получение данных по основному сайту
$params = [
    "viewID"     => "000000",
    "daysBefore" => "0",
    "metrics"    => "ga:transactions",
    "dimensions" => "ga:sourceMedium,ga:transactionId",
    "maxResults" => "5000"
];
$result = $analyticsObject->getData($params);
foreach ($result["rows"] as $transaction) {
    // todo
}