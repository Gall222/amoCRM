<?php
define('AMO_DOMAIN', 'amocrm.ru'); //домен CRM
define('AMO_PROTOCOL', 'https'); //протокол CRM
define('AUTO_BUILD', TRUE);

if (isset($_POST['name'])) {

    //---------------------------------авторизация-----------------------------------------------
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

    //-------------------------------------------------сделка-------------------------------------------------
    $leads['add'] = array(
        array(
            'name' => $_POST['name'],
            'created_at' => $_POST['created_at'],
            'sale' => $_POST['sale'],
        ),
    );

    $curl = curl_init(); #Сохраняем дескриптор сеанса cURL

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);           //TRUE для возврата результата передачи в качестве строки
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0'); //Содержимое заголовка "User-Agent: ", посылаемого в HTTP-запросе
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    );
    try {
        #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }

    $Response = json_decode($out, true);
    $Response = $Response['_embedded']['items'];
    foreach ($Response as $v) {
        if (is_array($v)) {
            $leadId = $v['id'] . PHP_EOL;               //Id сделки
        }
    }
    curl_close($curl);

//--------------------------------контакт------------------------------------------------
    $contacts['add'] = array(
        array(
            'name' => $_POST['contact_name'],
            'tags' => $_POST['contact_tags'],
            'leads_id' => array(
                trim($leadId),       //контакт привязан к сделке по id
            ),
        ),
    );

    $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/contacts';
    $curl = curl_init(); #Сохраняем дескриптор сеанса cURL

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    );
    try {
        #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }

    header("Location: {$_SERVER["PHP_SELF"]}");
    exit;
}
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
    <script>
        function validate_form(){
            if (document.form.name.value == "" || document.form.sale.value == "" || document.form.created_at.value == "")
                || document.form.contact_name.value == "" || document.form.contact_tags.value == ""
            {
                alert("Заполните все поля формы");
                return false;
            }
        else
            {
                return true;
            }
        }
    </script>
</head>
<body>

<form name="form" method="post" onsubmit="return validate_form();">
    <p><input name="name" placeholder="name"></p>
    <p><input name="created_at" placeholder="created at"></p>
    <p><input name="sale" placeholder="sale"></p>
    <p><input name="contact_name" placeholder="contact name"></p>
    <p><input name="contact_tags" placeholder="contact tags"></p>

    <button name="button">Send</button>
</form>

</body>
</html>