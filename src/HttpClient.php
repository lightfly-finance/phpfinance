<?php


namespace Lightfly\Finance;


use GuzzleHttp\Client;
use Lightfly\Finance\Exception\HttpException;

class HttpClient implements HttpClientInterface
{
    private $httpClient;

    /**
     * HttpClient constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @param $url
     * @param array $options
     * @return string
     * @throws HttpException
     */
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