<?php


namespace Lightfly\Finance;


use GuzzleHttp\Client;
use Lightfly\Finance\Exception\HttpException;

class HttpClient implements HttpClientInterface
{
    private $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

    public function get($url, $options = [])
    {
        $res = $this->httpClient->get($url, $options);

        $code = $res->getStatusCode();
        if ($code == 456) {
            throw new HttpException('Rate limit forbidden.');
        }

        return $res->getBody()->getContents();
    }
}