<?php

use GuzzleHttp\Client;
use Lightfly\Finance\Fund\Fund;
use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient();


//$stock = new Stock($httpClient);
//$data = $stock->longHuBang('2019-05-19', '2019-05-21');

$fund = new Fund($httpClient);
$data = $fund->internetBanking();

var_dump($data);

