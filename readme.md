金融数据服务

[![Build Status](https://travis-ci.com/twn39/phpfinance.svg?branch=master)](https://travis-ci.com/twn39/phpfinance)
[![Maintainability](https://api.codeclimate.com/v1/badges/bc35f8b7c6b61345a2d9/maintainability)](https://codeclimate.com/github/twn39/phpfinance/maintainability)

### Usage
```php
<?php
use GuzzleHttp\Client;
use Monster\Finance\HttpClient;
use Monster\Finance\Stock\Stock;

$httpClient = new HttpClient();

$stock = new Stock($httpClient);
$data = $stock->HS300();

var_dump($data);
```


### Run tests

    bin/phpspec run
