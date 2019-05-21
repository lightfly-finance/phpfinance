<?php


namespace Lightfly\Finance;


use GuzzleHttp\Client;

class HttpClient implements HttpClientInterface
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function get($url, $options = [])
    {
        $res = $this->httpClient->get($url, $options);

        return $res->getBody()->getContents();
    }

    public function post($url, $options = [])
    {
        $res = $this->httpClient->post($url, $options);

        return $res->getBody()->getContents();
    }
}