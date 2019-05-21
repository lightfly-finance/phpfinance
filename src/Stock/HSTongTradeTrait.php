<?php

namespace Lightfly\Finance\Stock;


use function iter\map;
use function iter\toArray;
use Lightfly\Finance\Exception\HSTongException;

/**
 * 沪深港股通数据
 * Trait HSTongTradeTrait
 * @package Monster\Finance\Stock
 */
trait HSTongTradeTrait
{
    static $BASE_URL = 'http://quotes.sina.cn/hq/api/openapi.php';

    static $HG_TONG_API = '/XTongService.getTopTongList?type=1';

    static $SG_TONG_API = '/XTongService.getTopTongList?type=3';

    static $HK_Tong_API = '/XTongService.getTongHoldingRatioList?type=hk';

    static $HSHK_REALTIME_TRADE_API = 'http://money.finance.sina.com.cn/quotes_service/api/jsonp.php/varliveDateTableList=/HK_MoneyFlow.getDayMoneyFlowOtherInfo';
    /**
     * 沪股通十大成交股
     * 数据源：http://stock.finance.sina.com.cn/hkstock/view/money_flow.php
     *
     * @return mixed
     * @throws HSTongException
     */
    public function HGTongTop10()
    {
        $res = $this->httpClient->get(self::$BASE_URL.self::$HG_TONG_API);
        $data = json_decode($res, true);

        if($data['result']['status']['code'] === 0) {
            return $data['result']['data'];
        }

        throw new HSTongException(json_encode($data));
    }

    /**
     * 深股通十大成交股
     * 数据源：http://stock.finance.sina.com.cn/hkstock/view/money_flow.php
     *
     * @return mixed
     * @throws HSTongException
     */
    public function SGTongTop10()
    {
        $res = $this->httpClient->get(self::$BASE_URL.self::$SG_TONG_API);
        $data = json_decode($res, true);

        if($data['result']['status']['code'] === 0) {
            return $data['result']['data'];
        }

        throw new HSTongException(json_encode($data));
    }

    /**
     * 港股通十大成交股
     * 数据源：http://stock.finance.sina.com.cn/hkstock/view/money_flow.php
     *
     * @return mixed
     * @throws HSTongException
     */
    public function HKTongTop10()
    {
        $res = $this->httpClient->get(self::$BASE_URL.self::$HK_Tong_API);
        $data = json_decode($res, true);

        if($data['result']['status']['code'] === 0) {
            return $data['result']['data'];
        }

        throw new HSTongException(json_encode($data));
    }

    /**
     * 沪深港通资金流向(实时)
     * 数据源： http://stock.finance.sina.com.cn/hkstock/view/money_flow.php
     */
    public function HSHKRealTimeTrade(): array
    {
        $res = $this->httpClient->get(self::$HSHK_REALTIME_TRADE_API);

        $pattern = "/\(\{(.+)\}\)/";
        preg_match($pattern, $res, $matches);

        $pattern = '/:\{([^{}]+)\}/';

        preg_match_all($pattern, $matches[1], $result);

        $data = map(function ($item) {
            $row = explode(',', $item);
            $tmp = [];
            foreach ($row as $value) {
                list($k, $v) = explode(':', $value);
                $tmp[$k] = trim($v, '"');
            }

            return $tmp;
        }, $result[1]);

        return toArray($data);
    }
}
