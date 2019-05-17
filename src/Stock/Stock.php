<?php
namespace Monster\Finance\Stock;


use function iter\map;
use function iter\toArray;
use Monster\Finance\HttpClientInterface;

class Stock
{
    use HSTongTradeTrait;
    use StockIndexTrait;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    static $HS300_API = 'http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?num=300&sort=symbol&asc=1&node=hs300';

    static $STOCK_HISTORY_API = 'http://finance.sina.com.cn/realstock/company';

    static $DAILY_HISTORY_API = 'http://quotes.money.163.com/service/chddata.html';

    /**
     * Stock constructor.
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 数据源： http://quotes.money.163.com/trade/lsjysj_600004.html?year=2019&season=1
     * @param $symbol
     * @param $dateFrom
     * @param $dateTo
     * @return array
     */
    public function dailyHistory($symbol, $dateFrom, $dateTo)
    {
        $queryString = http_build_query([
            'code' => "0$symbol",
            'start' => date('Ymd', strtotime($dateFrom)),
            'end' => date('Ymd', strtotime($dateTo)),
        ]);

        $url = self::$DAILY_HISTORY_API.'?'.$queryString.'&fields=TCLOSE;HIGH;LOW;TOPEN;LCLOSE;CHG;PCHG;TURNOVER;VOTURNOVER;VATURNOVER;TCAP;MCAP';

        return toArray($this->dailyKData($url));
    }

    /**
     * 每日历史数据
     *
     * @param $url
     * @return \Generator
     */
    private function dailyKData($url)
    {
        $res = $this->httpClient->get($url);

        $data = mb_convert_encoding($res, 'UTF-8', 'GB2312');

        $data = explode("\r\n", $data);

        foreach ($data as $item) {
            if (!empty($item)) {
                $row = explode(',', $item);
                $row[1] = trim($row[1], "'");
                yield $row;
            }
        }
    }

    /**
     * 获取股票每日复权历史数据
     *
     * @param $symbol string 前面有前缀sh或者sz
     * @param $type
     * @return array
     */
    public function history($symbol, $type)
    {
        $res = $this->httpClient->get(self::$STOCK_HISTORY_API."/$symbol/$type.js");

        $pattern = '/data:\{([0-9_ ",:.]+)\}/';

        preg_match($pattern, $res, $matches);
        $data = explode(',',$matches[1]);

        $tmp = [];
        foreach ($data as $item) {
            $pattern = '/^_(\d+)_(\d+)_(\d+):"([0-9.]+)"/';
            preg_match($pattern, $item, $result);

            $key = $result[1].'-'.$result[2].'-'.$result[3];
            $tmp[$key] = $result[4];
        }

        return $tmp;
    }

    /**
     * 数据源： http://vip.stock.finance.sina.com.cn/mkt/#hs300
     * 沪深300成分股
     *
     * @return array
     */
    public function HS300()
    {
        return toArray($this->getHS300Page());
    }

    /**
     * 获取沪深300分页数据
     *
     * @return \Generator
     */
    public function getHS300Page()
    {
        $page = 1;
        while (true) {
            $res = $this->httpClient->get(self::$HS300_API."&page=$page");

            $data = mb_convert_encoding($res, 'UTF-8', 'GB2312'); // 非json格式，为js对象格式，键名无引号

            if ($data === 'null') {
                break;
            }

            $pattern = '/\{([^{}]+)\}/';
            preg_match_all($pattern, $data, $matches);

            $data = map(function ($item) {
                $pattern = '/\{(.*)\}/';
                preg_match($pattern, $item, $result);
                $result = explode(',', $result[1]);
                $tmp = [];
                foreach ($result as $row) {
                    $data = explode(':', $row);
                    $tmp[$data[0]] = trim($data[1], '"');
                }

                return $tmp;
            }, $matches[0]);

            foreach ($data as $item) {

                yield [
                    'symbol' => $item['symbol'],
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'open' => $item['open'],
                    'high' => $item['high'],
                    'low' => $item['low'],
                    'volume' => $item['volume'],  // 成交量
                    'amount' => $item['amount'],  // 成交额
                    'trade' => $item['trade'],  // 最新价
                    'settlement' => $item['settlement'], // 昨收
                ];
            }

            $page += 1;
        }
    }
}