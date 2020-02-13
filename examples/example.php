<?php

use GuzzleHttp\Client;
use Lightfly\Finance\Fixer\Fixer;
use Lightfly\Finance\Fund\Fund;
use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient(new Client());

$fixer = new Fixer($httpClient, 'secret key');
$data = $fixer->all();
var_dump($data);





