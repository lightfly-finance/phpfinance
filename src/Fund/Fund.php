<?php
namespace Monster\Finance\Fund;


use Generator;
use function iter\toArray;
use Monster\Finance\HttpClient;
use function iter\map;

class Fund
{

    const API = 'http://stock.finance.sina.com.cn/fundInfo/api/openapi.php/CaihuiFundInfoService.getNav';

    const INTERNET_BANKING_API = 'http://quotes.money.163.com/fn/service/internetBanking.php';

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

    /**
     * 互联网理财产品
     * 数据源： http://quotes.money.163.com/old/#query=hlwlc
     */
    public function internetBanking()
    {
        $queryString = http_build_query([
            'sort' => 'CUR4',
            'order' => 'asc',
            'type' => 'FG',
            'count' => 50,
        ]);

        $queryString .= '&fields=NO,LI_CAI_MING_CHENG,FA_SHOU_SHANG,SYMBOL,SNAME,CUR4,CUR4_CHANGE,CURNAV_001,CURNAV_010,CURNAV_011,OFPROFILE8,PUBLISHDATE';

        $url = self::INTERNET_BANKING_API.'?'.$queryString;

        $res = $this->httpClient->get($url);

        $data = json_decode($res, true);

        return $data['list'];
    }

}