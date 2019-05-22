<?php


namespace Lightfly\Finance;


use GuzzleHttp\Client;

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

        return $res->getBody()->getContents();
    }
}