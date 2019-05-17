<?php

namespace Monster\Finance\Stock;

use function iter\filter;
use function iter\map;
use function iter\toArray;

trait StockIndexTrait
{

    static $STOCK_INDEX_API = 'http://hq.sinajs.cn/rn=mtnop&list=sh000001,sh000002,sh000003,sh000008,sh000009,sh000010,sh000011,sh000012,sh000016,sh000017,sh000300,sz399001,sz399002,sz399003,sz399004,sz399005,sz399006,sz399100,sz399101,sz399106,sz399107,sz399108,sz399333,sz399606';


    /**
     * 指数数据
     * 数据源：http://vip.stock.finance.sina.com.cn/mkt/#dpzs
     *
     * @return array
     */
    public function stockIndex()
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
}