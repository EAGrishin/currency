<?php

require_once 'CurrencyApi.php';

try {
    $api = new CurrencyApi();
    echo $api->run();
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}