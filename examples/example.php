<?php

use GuzzleHttp\Client;
use Monster\Finance\HttpClient;
use Monster\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient();


$stock = new Stock($httpClient);
$data = $stock->HS300();

var_dump($data);

