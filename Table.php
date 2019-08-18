<?php

define('AMO_DOMAIN', 'amocrm.ru'); //домен CRM
define('AMO_PROTOCOL', 'https'); //протокол CRM
define('AUTO_BUILD', TRUE);

$user = array(
    'USER_LOGIN' => 'galtsov222@gmail.com', #логин (электронная почта)
    'USER_HASH' => '23b0d5c226dbd729abf24f6626dfa00b23e7b552', #Хэш для доступа к API (смотрите в профиле пользователя)
);

$subdomain = 'galtsov222'; #Наш аккаунт - поддомен
#Формируем ссылку для запроса
$link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_COOKIEFILE, dirname
    (__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl, CURLOPT_COOKIEJAR, dirname
    (__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
$out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
curl_close($curl);

$start = microtime(TRUE);
$dateSort = date('D, d M Y H:i:s', strtotime("-1 months"));

$curl = curl_init(); #Сохраняем дескриптор сеанса cURL

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

curl_setopt($curl, CURLOPT_HTTPHEADER, array('IF-MODIFIED-SINCE: ' . $dateSort));

/* Выполняем запрос к серверу. */
$out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$Response = json_decode($out, true);

?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
    <style>
        td {
            text-align: center
        }
    </style>
</head>
<body>

<table border="1" align="center" width="600">
    <tr style="font-weight:bold">
        <td>Id</td>
        <td>Name</td>
        <td>Sale</td>
    </tr>
    <?php foreach ($Response['_embedded']['items'] as $res) : ?>
        <tr>
            <td><?= $res['id'] ?></td>
            <td><?= $res['name'] ?></td>
            <td><?= $res['sale'] ?></td>
        </tr>
    <? endforeach; ?>
</table>


</body>
</html>