<?php

use GuzzleHttp\Client;
use Lightfly\Finance\Fixer\Fixer;
use Lightfly\Finance\Fund\Fund;
use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;

require __DIR__ . '/../vendor/autoload.php';

$httpClient = new HttpClient(new Client());

$fixer = new Fixer($httpClient, '29c96e229e98942d24888c0b5c8e9050');
$data = $fixer->all();





