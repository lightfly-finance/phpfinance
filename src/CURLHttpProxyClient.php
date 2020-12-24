<?php

namespace Lightfly\Finance;

use GuzzleHttp\Client;
use Lightfly\Finance\Exception\HttpException;

class CURLHttpProxyClient implements HttpClientInterface
{
    private $httpClient;

    private $httpProxy;

    private $noProxy;

    /**
     * HttpClient constructor.
     * @param Client $client
     * @param string $httpProxy
     * @param array $noProxy
     */
    public function __construct(Client $client, string $httpProxy, $noProxy = [])
    {
        $this->httpClient = $client;
        $this->httpProxy = $httpProxy;
        $this->noProxy = $noProxy;
    }

    /**
     * @param $url
     * @param array $options
     * @return string
     * @throws HttpException
     */
    public function get($url, $options = []): string
    {
        $options['proxy'] = [
            'http' => $this->httpProxy,
            'no' => $this->noProxy,
        ];
        $res = $this->httpClient->get($url, $options);

        $code = $res->getStatusCode();
        if ($code == 456) {
            throw new HttpException('Rate limit forbidden.');
        }

        return $res->getBody()->getContents();
    }

}
