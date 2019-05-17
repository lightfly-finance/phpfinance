<?php

use GuzzleHttp\Client;
use Monster\Finance\HttpClient;
use Monster\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient();


$stock = new Stock($httpClient);
$data = $stock->dailyHistory('600365', '2019-01-01', '2019-05-17');

var_dump($data);

