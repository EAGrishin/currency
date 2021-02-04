<?php

abstract class Api
{
    public string $apiName = ''; //currency

    protected $method = ''; //GET|POST

    public $requestUri = [];
    public $requestParams = [];

    protected $action = ''; //Название метод для выполнения


    public function __construct() {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        //Массив GET параметров разделенных слешем
        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
        $this->requestParams = $_REQUEST;

        //Определение метода запроса
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function run() {
        //Первые 2 элемента массива URI должны быть "api" и название таблицы
        if(array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== $this->apiName){
            throw new RuntimeException('API Not Found', 404);
        }
        //Определение действия для обработки
        $this->action = $this->getAction();

        //Если метод(действие) определен в дочернем классе API
        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    protected function response($data, $status = 500) {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }

    // запрос в API ЦБ на получение курса валют
    protected function request() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.cbr.ru/scripts/XML_daily.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $curlData = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            libxml_use_internal_errors(false);
            $xml = simplexml_load_string($curlData);
            if (!($xml instanceof SimpleXMLElement)) {
                throw new RuntimeException('Error XML_daily.asp', 405);
            }
        } else {
            throw new RuntimeException('Invalid request http://www.cbr.ru/scripts/XML_daily.asp', $http_code);
        }

        return $xml;
    }

    private function requestStatus($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code])?$status[$code]:$status[500];
    }

    protected function getAction() {
        $method = $this->method;
        switch ($method) {
            case 'GET':
            case 'POST':
                if ($this->requestUri) {
                    return 'actionView';
                } else {
                    return 'actionIndex';
                }
                break;
            default:
                return null;
        }
    }

    abstract protected function actionIndex();
    abstract protected function actionView();

}