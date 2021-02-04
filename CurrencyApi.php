<?php

require_once 'Api.php';
require_once 'Currency.php';
require_once 'config/database.php';

class CurrencyApi extends Api
{
    public string $apiName = 'currency';
    private PDO $db;

    //Список всех кодов валют
    private array $currencies = ['AUD', 'AZN', 'GBP', 'AMD', 'BYN', 'BGN', 'BRL', 'HUF', 'HKD', 'DKK', 'USD', 'EUR', 'INR', 'KZT', 'CAD', 'KGS', 'CNY', 'MDL', 'NOK', 'PLN', 'RON', 'XDR', 'SGD', 'TJS', 'TRY', 'TMT', 'UZS', 'UAH', 'CZK', 'SEK', 'CHF', 'ZAR', 'KRW', 'JPY'];


    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        parent::__construct();
    }

    /**
     * Метод GET
     * Вывод списка всех записей на текущую дату
     * http://ДОМЕН/api/currency
     * @return string
     */
    public function actionIndex(): string {
        $currency = new Currency($this->db);

        // Получаем все данные по курсу на текущую дату
        if ( $data = $currency->getAll() ) {
            return $this->response($data, 200);
        }
        // Если данных нет в базе, то делаем запрос к API ЦБ и добавляем в базу
        elseif( $data = $this->getCurrency() ) {
            $currency->insert($data);
            return $this->response($data, 200);
        }

        return $this->response('Data not found', 404);
    }

    /**
     * Метод GET
     * Просмотр отдельной записи (по code) на текущую дату
     * http://ДОМЕН/api/currency/usd
     * @return string
     */
    public function actionView(): string {
        //code должен быть первым параметром после /currency/x
        $code = trim(array_shift($this->requestUri));

        if($code && in_array(strtoupper($code), $this->currencies)) {
            $currency = new Currency($this->db);

            // Получаем данные по оределенному курсу на текущую дату
            if ( $data = $currency->getOne($code) ) {
                return $this->response($data, 200);
            }
            // Если данных нет в базе, то делаем запрос к API ЦБ и добавляем в базу
            elseif( $data = $this->getCurrency() ) {
                //Добавляем или обновляем данные в базе
                $currency->insert($data);
                if ( $data = $currency->getOne($code) ) {
                    return $this->response($data, 200);
                }
            }
        }
        return $this->response('Data not found', 404);
    }

    private function getCurrency(): array {
        $xml = $this->request();
        $currency = [];
        foreach ($xml->Valute as $v) {
            $currency[] = [
                "code" => (String) $v->CharCode,
                "date" => date("Y-m-d", strtotime(trim($xml->attributes()->Date))),
                "rate" => str_replace(',', '.', $v->Value)
            ];
        }
        return $currency;
    }

}