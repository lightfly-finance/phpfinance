<?php
namespace Monster\Finance\Fund;


use Generator;
use function iter\toArray;
use Monster\Finance\HttpClient;
use function iter\map;

class Fund
{

    const API = 'http://stock.finance.sina.com.cn/fundInfo/api/openapi.php/CaihuiFundInfoService.getNav';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Fund constructor.
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {

        $this->httpClient = $httpClient;
    }

    /**
     * 数据源：http://finance.sina.com.cn/fund/quotes/006478/bc.shtml
     *
     * @param $symbol
     * @param $dateFrom
     * @param $dateTo
     * @return array
     */
    public function get($symbol, $dateFrom, $dateTo)
    {
        $queryString = http_build_query([
            'symbol' => $symbol,
            'datefrom' => $dateFrom,
            'dateto' => $dateTo,
        ]);
        $url = self::API."?".$queryString;

        $data = [];

        foreach ($this->getFundPage($url) as $item) {
            $data[$item['fbrq']] = $item;
        }

        return toArray(map(function ($item) {
            return [
                '日期' => $item['fbrq'],
                '单位净值（元）' => $item['jjjz'],
                '累计净值（元）' => $item['ljjz'],
            ];
        }, $data));

    }

    /**
     * @param $url
     * @return Generator
     */
    private function getFundPage($url)
    {
        $page = 1;
        while (true) {
            $res = $this->httpClient->get($url."&page=$page");
            $data = json_decode($res, true);

            if($data['result']['status']['code'] === 0) {
                if (empty($data['result']['data']['data'])) {
                    break;
                }
                foreach ($data['result']['data']['data'] as $item) {
                    yield $item;
                }
                $page += 1;
            }

        }
    }

}