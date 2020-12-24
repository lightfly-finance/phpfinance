<?php


namespace Lightfly\Finance;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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
    public function get($url, $options = []): string
    {
        $options['headers'] = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:76.0) Gecko/20100101 Firefox/76.0',
        ];
        try {
            $res = $this->httpClient->get($url, $options);
            return $res->getBody()->getContents();

        } catch (ClientException $error) {
            $code = $error->getCode();
            if ($code == 456) {
                throw new HttpException('The Client is blocked by sina.');
            }
        }
    }
}
