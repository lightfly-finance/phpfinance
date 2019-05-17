<?php


namespace Monster\Finance\Stock;


use function iter\map;
use function iter\toArray;
use function iter\toIter;
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

    public function __construct(HttpClientInterface $httpClient)
    {

        $this->httpClient = $httpClient;
    }

    public function HS300()
    {
        return toArray($this->getHS300Page());
    }

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