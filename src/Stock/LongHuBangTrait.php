<?php


namespace Monster\Finance\Stock;


trait LongHuBangTrait
{

    static $LONG_HU_BANG_API = 'http://quotes.money.163.com/hs/marketdata/service/lhb.php';

    /**
     * 龙虎榜数据
     * 数据源： http://quotes.money.163.com/old/#query=MRLHB&DataType=lhb&sort=TDATE&order=desc&count=150&page=0
     * @param $startTime
     * @param $endTime
     * @return array
     */
    public function longHuBang(string $startTime, string $endTime)
    {
        // todo： 有分页，添加分页
        $queryString = http_build_query([
            'page' => 0,
            'sort' => 'TDATE',
            'order' => 'desc',
            'count' => 150,
            'type' => 'query',
        ]);

        $segment = '&fields=NO,SYMBOL,SNAME,TDATE,TCLOSE,PCHG,SMEBTSTOCK1,SYMBOL,VOTURNOVER,COMPAREA,VATURNOVER,SYMBOL';
        $segment .= "&query=start:$startTime;end:$endTime";
        $url = self::$LONG_HU_BANG_API.'?'.$queryString.$segment;

        $res = $this->httpClient->get($url);

        return json_decode($res, true);
    }
}