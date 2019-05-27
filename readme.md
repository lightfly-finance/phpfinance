金融数据服务

![GitHub](https://img.shields.io/github/license/twn39/phpfinance.svg?style=flat-square)
[![Packagist Version](https://img.shields.io/packagist/v/lightfly/finance.svg?style=flat-square)](https://packagist.org/packages/lightfly/finance)
[![Build Status](https://travis-ci.com/lightfly-finance/phpfinance.svg?branch=master)](https://travis-ci.com/lightfly-finance/phpfinance)


[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=lightfly-finance_phpfinance)](https://sonarcloud.io/dashboard?id=lightfly-finance_phpfinance)

### 安装

``` 
composer require guzzlehttp/guzzle
composer require lightfly/finance
```

### 使用

*基本使用*

```php
<?php
use GuzzleHttp\Client;
use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;

$httpClient = new HttpClient(new Client());

$stock = new Stock($httpClient);
$data = $stock->HS300();

var_dump($data);
```

*高级*

如果不喜欢 guzzle 或者主机配置的原因，可以替换 http client 的实现，只要继承 HttpClientInterface 实现相应的 get 和 post 方法即可，示例：

```php 
class AnotherHttpClient implements HttpClientInterface
{
    public function get($url, $options = [])
    {
        return file_get_contents($url);
    }
}

$httpClient = new AnotherHttpClient();

$stock = new Stock($httpClient);
$data = $stock->HS300();

var_dump($data);

```


### Run tests

    bin/phpspec run
