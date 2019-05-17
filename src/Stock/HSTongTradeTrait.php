<?php

namespace Monster\Finance\Stock;


trait HSTongTradeTrait
{
    static $BASE_URL = 'http://quotes.sina.cn/hq/api/openapi.php';

    static $HG_TONG_API = '/XTongService.getTopTongList?type=1';

    static $SG_TONG_API = '/XTongService.getTopTongList?type=3';

    static $HK_Tong_API = '/XTongService.getTongHoldingRatioList?type=hk';

    /**
     * 沪股通十大成交股
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
}
