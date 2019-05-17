金融数据服务


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
