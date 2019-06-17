<?php


namespace Lightfly\Finance\Fixer;


use Lightfly\Finance\Exception\FixerException;
use Lightfly\Finance\HttpClientInterface;

class Fixer
{

    private $httpClient;

    private $apiKey;

    const BASE_URL = "http://data.fixer.io/api";

    /**
     * Fixer constructor.
     * @param HttpClientInterface $httpClient
     * @param string $apiKey
     */
    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     * @throws FixerException
     */
    public function all()
    {
        $queryString = http_build_query([
            'access_key' => $this->apiKey,
        ]);
        $url = self::BASE_URL.'/latest?'.$queryString;

        $data = $this->httpClient->get($url);
        $result = json_decode($data, true);
        if($result['success'] === true) {
            return $result;
        }

        throw new FixerException($result['error'], $result['error']['code']);
    }

}