<?php

use GuzzleHttp\Client;
use Lightfly\Finance\Fund\Fund;
use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient(new Client());

$stock = new Stock($httpClient);
$data = $stock->HSHKRealTimeTrade();

var_dump($data);

