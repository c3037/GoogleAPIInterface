<?php

namespace c3037\Google;

/**
 * Класс для работы с Google API
 */
abstract class Base
{
    /**
     * @var string Аккаунт пользователя
     */
    protected $clientEmail;

    /**
     * @var string Приватный ключ пользователя
     */
    protected $privateKey;

    /**
     * @var string Идентифицирующий токен
     */
    protected $accessToken;

    /**
     * Запрашиваемые права доступа
     */
    const SCOPE = "abstract";

    /**
     * @param null $clientSecretsFile
     * @throws Exception
     */
    public function __construct($clientSecretsFile = null)
    {
        // проверяем наличие конфигурационного файла
        if (!$clientSecretsFile or !file_exists($clientSecretsFile)) {
            throw new \Exception("Файл '{$clientSecretsFile}' не найден");
        }
        $client_secrets = json_decode(file_get_contents($clientSecretsFile), true);

        // проверяем валидность конфигурационного файла
        if (!isset($client_secrets["client_email"]) or !isset($client_secrets["private_key"])) {
            throw new \Exception("Файл '{$clientSecretsFile}' не валиден");
        }

        // устанавливаем переменные для аутентификации
        $this->clientEmail = $client_secrets["client_email"];
        $this->privateKey = $client_secrets["private_key"];

        $this->getAuth();
    }

    /**
     * Аутентификация
     * @throws Exception
     */
    protected function getAuth()
    {
        // формируем запрос на аутентификацию
        $header = $this->getAuthRequestHeader();
        $claimSet = $this->getAuthRequestClaimSet();
        $signature = $this->getAuthRequestSignature($header, $claimSet);
        $grantType = urlencode('urn:ietf:params:oauth:grant-type:jwt-bearer');
        $assertion = urlencode($header . "." . $claimSet . "." . $signature);

        // отправляем запрос на аутентификацию
        $response = $this->sendAuthRequest($grantType, $assertion);
        if (!isset($response["access_token"])) {
            throw new \Exception("Ошибка аутентификации");
        }

        // устанавливаем токен
        $this->accessToken = $response["access_token"];
    }

    /**
     * Заголовок запроса на аутентификацию
     * @return string
     */
    protected function getAuthRequestHeader()
    {
        $header = [
            "alg" => "RS256",
            "typ" => "JWT",
        ];
        return base64_encode(json_encode($header));
    }

    /**
     * Тело запроса на аутентификацию
     * @return string
     */
    protected function getAuthRequestClaimSet()
    {
        $claimSet = [
            "iss"   => $this->clientEmail,
            "scope" => static::SCOPE,
            "aud"   => "https://www.googleapis.com/oauth2/v4/token",
            "exp"   => time() + 3600,
            "iat"   => time(),
        ];
        return base64_encode(json_encode($claimSet));
    }

    /**
     * Цифровая подпись запроса на аутентификацию
     * @param string $header
     * @param string $claimSet
     * @return string
     */
    protected function getAuthRequestSignature($header, $claimSet)
    {
        $signature = "";
        $pkeyID = openssl_pkey_get_private($this->privateKey);
        openssl_sign($header . "." . $claimSet, $signature, $pkeyID, "SHA256");
        openssl_free_key($pkeyID);
        return base64_encode($signature);
    }

    /**
     * Отправка запроса на аутентификацию
     * @param $grantType
     * @param $assertion
     * @return mixed
     */
    protected function sendAuthRequest($grantType, $assertion)
    {
        $authUrl = 'https://www.googleapis.com/oauth2/v4/token';
        $ch = curl_init($authUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type={$grantType}&assertion={$assertion}");

        $headers = [];
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);

        return is_array($response) ? $response : [];
    }

    /**
     * Получение данных через API
     * @param array $params
     * @return mixed
     */
    public abstract function getData($params = []);

}