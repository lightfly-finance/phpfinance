<?php

namespace Monster\Finance\Stock;

use Generator;
use function iter\filter;
use function iter\map;
use function iter\toArray;


/**
 * 指数数据
 * Trait StockIndexTrait
 * @package Monster\Finance\Stock
 */
trait StockIndexTrait
{

    static $STOCK_INDEX_API = 'http://hq.sinajs.cn/rn=mtnop&list=sh000001,sh000002,sh000003,sh000008,sh000009,sh000010,sh000011,sh000012,sh000016,sh000017,sh000300,sz399001,sz399002,sz399003,sz399004,sz399005,sz399006,sz399100,sz399101,sz399106,sz399107,sz399108,sz399333,sz399606';

    static $SH_INDEX_COMPONENT_STOCKS = "http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeDataSimple";

    /**
     * 指数数据
     * 数据源：http://vip.stock.finance.sina.com.cn/mkt/#dpzs
     *
     * @return array
     */
    public function stockIndex(): array
    {
        $res = $this->httpClient->get(self::$STOCK_INDEX_API);
        $res = mb_convert_encoding($res, 'UTF-8', 'GBK');

        $data = explode(";", $res);

        $filter = filter(function ($item) {
            return !empty(trim($item));
        }, $data);

        $result = map(function ($row) {
            $pattern = '/hq_str_(s[z|h][0-9]+)="(.*)"/';
            $match = preg_match($pattern, $row, $matches);

            if ($match) {
                return [
                    'code' => $matches[1],
                    'list' => explode(',', $matches[2]),
                ];
            }
        }, $filter);

        return toArray(map(function ($item) {
            return [
                '代码' => $item['code'],
                '名称' => $item['list'][0],
                '今开' => $item['list'][1],
                '昨收' => $item['list'][2],
                '最新价' => $item['list'][3],
                '最高' => $item['list'][4],
                '最低' => $item['list'][5],
                '成交量' => $item['list'][8],  // 单位: 手
                '成交额' => $item['list'][9],  // 单位: 万
            ];
        }, $result));

    }

    /**
     * 上证指数成分股
     * 数据源： http://vip.stock.finance.sina.com.cn/mkt/#zhishu_000001
     */
    public function SHIndexComponentStocks()
    {
        $symbol = '000001';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * 返回迭代器
     * @return Generator
     */
    public function SHIndexComponentStocksIter()
    {
        $symbol = '000001';
        return $this->getComponentStocksPage($symbol);
    }

    /**
     * 上证50指数成分股
     */
    public function SH50IndexComponentStocks()
    {
        $symbol = '000016';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * 上证消费指数成分股
     */
    public function SHConsumptionIndexComponentStocks()
    {
        $symbol = '000036';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * 上证医药指数成分股
     */
    public function SHMedicineIndexComponentStocks()
    {
        $symbol = '000037';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * 深证成指成分股
     */
    public function SZIndexComponentStocks()
    {
        $symbol = '399001';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * @return Generator
     */
    public function SZIndexComponentStocksIter()
    {
        $symbol = '399001';
        return $this->getComponentStocksPage($symbol);
    }
    /**
     * 深证综指成分股
     * @return array
     */
    public function SZCompositeIndexComponentStocks()
    {
        $symbol = '399106';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * 返回迭代器
     * @return Generator
     */
    public function SZCompositeIndexComponentStocksIter()
    {
        $symbol = '399106';
        return $this->getComponentStocksPage($symbol);
    }

    /**
     * 中证500指数成分股
     */
    public function ZZ500IndexComponentStocks()
    {
        $symbol = '000905';
        return toArray($this->getComponentStocksPage($symbol));
    }

    /**
     * @return Generator
     */
    public function ZZ500IndexComponentStocksIter()
    {
        $symbol = '000905';
        return $this->getComponentStocksPage($symbol);
    }

    /**
     * 上证指数成分股分页数据
     * @return Generator
     */
    private function getComponentStocksPage($symbol)
    {
        $page = 1;
        $num = 80;

        while (true) {
            $queryString = http_build_query([
                'page' => $page,
                'num' => $num,
                'sort' => 'symbol',
                'asc' => 1,
                'node' => 'zhishu_'.$symbol,
            ]);

            $url = self::$SH_INDEX_COMPONENT_STOCKS.'?'.$queryString;

            $res = $this->httpClient->get($url);

            $res = mb_convert_encoding($res, 'UTF-8', 'GBK');

            if ($res === 'null' || empty($res)) {
                break;
            }

            $pattern = '/\{([^{}]+)\}/';

            preg_match_all($pattern, $res, $matches);

            foreach ($matches[1] as $company) {
                $params = explode(',', $company);

                $tmp = [];
                foreach ($params as $param) {
                    $pattern = '/^([a-z]+):(.+)/';
                    preg_match($pattern, $param, $result);
                    $tmp[$result[1]] = trim($result[2], '"');
                }

                yield $tmp;
            }

            $page += 1;
        }

    }
}