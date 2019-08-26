<?php

define('AMO_DOMAIN', 'amocrm.ru'); //домен CRM
define('AMO_PROTOCOL', 'https'); //протокол CRM
define('AUTO_BUILD', TRUE);

class Form
{

    public $user = array(
        'USER_LOGIN' => 'galtsov222@gmail.com', #логин (электронная почта)
        'USER_HASH' => '23b0d5c226dbd729abf24f6626dfa00b23e7b552', #Хэш для доступа к API (смотрите в профиле пользователя)
    );

    public $subdomain = 'galtsov222'; #Наш аккаунт - поддомен
    public function init()
    {
        #Формируем ссылку для запроса
        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/leads';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->user));
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
    }

    public function isDuplicate($post)   //Основная ф-ция
    {
        $this->init();
        //Получаем список сделок
        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/leads?query=' . $post['nameOfSale'];
        $dateSort = date('D, d M Y H:i:s', strtotime("-1 months"));
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('IF-MODIFIED-SINCE: ' . $dateSort));
        /* Выполняем запрос к серверу. */
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $leadsResponse = json_decode($out, true);
        $leadsResponse = $leadsResponse['_embedded']['items'];

        //Получаем контакты
        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/contacts?query=' . $post['contactName'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-undefined/2.0");
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
        $out = curl_exec($curl);
        curl_close($curl);
        $contsctsResponse = json_decode($out, true);
        $contsctsResponse = $contsctsResponse['_embedded']['items'];

        if ($leadsResponse != null) { //есть такая сделка
            $this->makeTask($leadsResponse[0]['id']);
        } else {
            if ($contsctsResponse == null) { //нет сделки и контакта
                $this->makeSaleAndContact($post, $this->subdomain);
                $this->webHook($leadsResponse[0]['id']);
            } else {  //нет сделки, есть контакт
                $this->addLeadOnly($post, $this->subdomain, $contsctsResponse[0]['id']);
                $this->webHook($leadsResponse[0]['id']);
            }
        }
    }

    public function addLeadOnly($post, $subdomain, $clientId)   //Добавить только сделку
    {
        $leads['add'] = array(
            array(
                'name' => $post['nameOfSale'],
                'sale' => $post['cost'],
                'contacts_id' =>
                    array(
                        0 => $clientId,        //прикрепляем к айди контакта
                    ),
                'custom_fields' =>
                    array(
                        0 =>
                            array(
                                'id' => '261879',
                                'values' =>
                                    array(
                                        0 =>
                                            array(
                                                'value' => $post['customField'],
                                            ),
                                    ),
                            ),
                    ),
            )
        );

        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';
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
        $this->webHook($leadId);    //Сзранить данные сделки
        return $leadId;
    }

    public function makeSaleAndContact($post, $subdomain)          //создать сделку и новый контакт
    {
        $leadId = $this->addLeadOnly($post, $subdomain, null);

                //--------------------------------контакт------------------------------------------------
        $contacts['add'] = array(
            array(
                'name' => $post['contactName'],
                'leads_id' => array(
                    trim($leadId),       //контакт привязан к сделке по id
                ),
                'custom_fields' =>
                    array(
                        0 =>
                            array(
                                'id' => '187255',
                                'values' =>
                                    array(
                                        0 =>
                                            array(
                                                'value' => $post['contactPost'],
                                            ),
                                    ),
                            ),
                    )
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

    public function makeTask($saleId)              //Создать задание позвонить клиенту
    {
        $data = array(
            'add' =>
                array(
                    0 =>
                        array(
                            'element_id' => $saleId,
                            'element_type' => '2',
                            'complete_till' => '1562216134',
                            'task_type' => '1',
                            'text' => 'Связаться с клиентом',
                        ),
                ),
        );
        $link = "https://" . $this->subdomain . ".amocrm.ru/api/v2/tasks";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-undefined/2.0");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
        $out = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($out, TRUE);
    }
            //Веб-хук
    public function webHook($leadId) //Получаем новую сделку по ID и сохраняем ее в файл
    {
//        $link = 'http://webhook.site/f3c008d6-3e0e-4f88-b43f-fd7f9fc9bda6';
        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/leads?id=' . (int)$leadId;

        $headers[] = "Accept: application/json";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-undefined/2.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__)."/cookie.txt");
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__)."/cookie.txt");
        $out = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($out,TRUE);

        $toSave = $result['_embedded']['items'][0];
        $str = '';

        foreach ($toSave as $key => $value ) {
            $str .= $key . ';' . $value . ";\r\n";
        }
        file_put_contents('log.csv', $str);
    }
}
