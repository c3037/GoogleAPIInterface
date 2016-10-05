<?php

namespace c3037\Google;

/**
 * Класс для работы с Google Analytics через API
 * Руководство: https://developers.google.com/analytics/devguides/reporting/core/v3/
 */
class Analytics extends Base
{
    /**
     * Запрашиваемые права доступа
     */
    const SCOPE = "https://www.googleapis.com/auth/analytics.readonly";

    /**
     * Извлечение данных из GA
     * @param array $params
     * @return array|mixed
     */
    public function getData($params = [])
    {
        $paramsDefault = ["viewID" => "", "daysBefore" => "", "metrics" => "", "dimensions" => "", "maxResults" => ""];
        $params = array_merge($paramsDefault, $params);

        $viewID = (int)$params["viewID"];
        $daysBefore = (int)$params["daysBefore"];
        $date = (new \DateTime())->sub(new \DateInterval("P{$daysBefore}D"))->format('Y-m-d');
        $metrics = (string)$params["metrics"];
        $dimensions = (string)$params["dimensions"];
        $maxResults = (int)$params["maxResults"];

        $url = "https://www.googleapis.com/analytics/v3/data/ga"
            . "?ids=ga:{$viewID}"
            . "&start-date={$date}"
            . "&end-date={$date}"
            . "&metrics={$metrics}"
            . "&dimensions={$dimensions}"
            . "&max-results={$maxResults}"
            . "&access_token={$this->accessToken}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);

        return is_array($response) ? $response : [];
    }
}