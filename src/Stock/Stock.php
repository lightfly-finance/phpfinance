<?php
namespace Lightfly\Finance\Stock;


use DateTime;
use Generator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use function iter\map;
use function iter\toArray;
use Lightfly\Finance\Exception\StockException;
use Lightfly\Finance\HttpClientInterface;

class Stock
{
    use HSTongTradeTrait;
    use StockIndexTrait;
    use StockIndicatorTrait;
    use LongHuBangTrait;
    use RankTrait;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    static $HS300_API = 'http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?num=300&sort=symbol&asc=1&node=hs300';

    static $STOCK_HISTORY_API = 'http://finance.sina.com.cn/realstock/company';

    static $DAILY_HISTORY_API = 'http://quotes.money.163.com/service/chddata.html';

    static $SUGGEST_API = 'http://suggest3.sinajs.cn/suggest/';

    static $REALTIME_API = 'http://hq.sinajs.cn/list';

    static $BOARD_API = 'http://hq.stock.sohu.com/pl';

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
     * @param $symbol string 需要前缀sh or sz
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     * @throws StockException
     */
    public function dailyHistory(string $symbol, string $dateFrom, string $dateTo): array
    {
        $prefix = substr($symbol, 0, 2);

        // 代码为股票代码，上海股票前加0，如600756变成0600756，深圳股票前加1
        if ('sh' === strtolower($prefix)) {
            $prefixSymbol = '0'.substr($symbol, 2, strlen($symbol) - 2);
        } elseif ('sz' === strtolower($prefix)) {
            $prefixSymbol = '1'.substr($symbol, 2, strlen($symbol) - 2);
        } else {
            throw new StockException('symbol format invalid');
        }
        $queryString = http_build_query([
            'code' => $prefixSymbol,
            'start' => date('Ymd', strtotime($dateFrom)),
            'end' => date('Ymd', strtotime($dateTo)),
        ]);

        $url = self::$DAILY_HISTORY_API.'?'.$queryString.'&fields=TCLOSE;HIGH;LOW;TOPEN;LCLOSE;CHG;PCHG;TURNOVER;VOTURNOVER;VATURNOVER;TCAP;MCAP';

        return toArray($this->dailyKData($url));
    }

    /**
     * 每日历史数据
     *
     * @param string $url
     * @return Generator
     */
    private function dailyKData(string $url): Generator
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
     * @deprecated
     * @param $symbol string 前面有前缀sh或者sz
     * @param $type string  前复权: qfq , 后复权: hfq
     * @return array
     */
    public function history(string $symbol, string $type): array
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
     * @deprecated
     * @return array
     */
    public function HS300(): array
    {
        return toArray($this->getHS300Page());
    }

    /**
     * 获取沪深300分页数据
     * @deprecated
     * @return Generator
     */
    public function getHS300Page()
    {
        $page = 1;
        while (true) {
            $res = $this->httpClient->get(self::$HS300_API."&page=$page");

            $data = mb_convert_encoding($res, 'UTF-8', 'GB2312'); // 非json格式，为js对象格式，键名无引号

            if ($data === 'null' || empty($data)) {
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

    /**
     * 关键字搜索对应股票或者其他名称
     *
     * @param $keyword
     * @return array
     */
    public function suggest($keyword): array
    {
        $url = self::$SUGGEST_API.'key='.$keyword;

        $res = $this->httpClient->get($url);
        $res = mb_convert_encoding($res, 'UTF-8', 'GBK');

        $pattern = '/="(.+)"/';

        preg_match($pattern, $res, $matches);
        $data = explode(';', $matches[1]);

        return toArray(map(function ($item) {
            return explode(',', $item);
        }, $data));
    }

    /**
     * 最新行情
     * @deprecated
     * @param array $symbols
     * @return array
     */
    public function realTimeStock(array $symbols): array
    {
        $symbolStr = implode(',', $symbols);

        $url = self::$REALTIME_API.'='.$symbolStr;

        $res = $this->httpClient->get($url);
        $res = mb_convert_encoding($res, 'UTF-8', 'GBK');

        $pattern = '/hq_str_(s[z|h][0-9]{6})="(.+)";/';
        preg_match_all($pattern, $res, $matches);

        $data = [];
        $length = count($symbols);
        $header = [
            '代码', '名称', '今开', '昨收', '当前价格', '最高', '最低', '成交量', '成交额',
            '买一申请', '买一报价', '买二申请', '买二报价', '买三申请', '买三报价',
            '买四申请', '买四报价', '买五申请', '买五报价', '卖一申请', '卖一报价',
            '卖二申请', '卖二报价', '卖三申请', '卖三报价', '卖四申请', '卖四报价', '卖五申请',
            '卖五报价', '日期',
        ];
        $data[] = $header;

        for($i = 0; $i < $length; $i++) {

            $item = explode(',', $matches[2][$i]);

            $data[] = [
                $matches[1][$i],
                $item[0], $item[1],
                $item[2], $item[3],
                $item[4], $item[5],
                $item[8], $item[9],
                $item[10], $item[11],  // 买一
                $item[12], $item[13],  // 买二
                $item[14], $item[15],
                $item[16], $item[17],
                $item[18], $item[19],
                $item[20], $item[21], // 卖一
                $item[22], $item[23], // 卖二
                $item[24], $item[25],
                $item[26], $item[27],
                $item[28], $item[29], // 卖五
                $item[30].' '.$item[31] // 日期
            ];
        }

        return $data;
    }

    /**
     * 板块
     * 数据源：http://q.stock.sohu.com/cn/bk.shtml
     *
     * @return array
     */
    public function board(): array
    {
        $url = self::$BOARD_API.'/pl-1.html';
        $data = $this->httpClient->get($url);

        $data = mb_convert_encoding($data, 'UTF-8', 'GBK');

        $pattern = "/ODIA\(\[(.+)\]\)/";

        preg_match($pattern, $data, $matches);

        $pattern = "/\[([^]]+)\]/";

        preg_match_all($pattern, $matches[1], $result);

        $data = map(function ($item) {
            $params = explode(',', $item);

            return toArray(map(function ($param) {
                return trim($param, "'");
            }, $params));
        }, $result[1]);

        $header = [
            "代码", "板块", "公司家数", "平均价格", "平均涨跌额", "平均涨跌幅", "总手",
            "总成交额", "领涨股", "当前价", "涨跌额", "涨跌幅",
        ];

        $result = toArray($data);

        array_unshift($result, $header);

        return $result;
    }

    /**
     * 板块成分股
     * 数据源：http://q.stock.sohu.com/cn/bk_4304.shtml
     * @param string $code
     * @return array
     */
    public function boardStocks(string $code): array
    {
        $url = self::$BOARD_API."/$code-1.html";

        $data = $this->httpClient->get($url);

        $data = mb_convert_encoding($data, 'UTF-8', 'GBK');

        $pattern = "/ODIA\(\[(.+)\]\)/";

        preg_match($pattern, $data, $matches);

        $pattern = "/\[([^]]+)\]/";

        preg_match_all($pattern, $matches[1], $result);

        $data = $result[1];
        array_shift($data);

        $result = map(function ($item) {

            $params = explode(',', $item);

            return toArray(map(function($param) {
                return trim($param, "'");
            }, $params));

        }, $data);

        $header = [
            "股票代码", "股票名称", "当前价", "涨跌额", "涨跌幅", "现手", "总手",
            "成交金额", "换手率", "今低", "今高", "今开盘", "昨收盘",
        ];

        $data = toArray($result);
        array_unshift($data, $header);

        return $data;
    }

    /**
     * 沪深A股
     * 数据来源： http://stockapp.finance.qq.com/mstats/?pgv_ref=fi_quote_navi_bar#mod=list&id=ssa&module=SS&type=ranka
     * @param string $tmpDir
     * @return array
     * @throws \Exception
     */
    public function HSTotal(string $tmpDir = '.'): array
    {
        $updateDate = '';
        $data = [];
        foreach ($this->HSTotalGenerator($tmpDir) as $key => $row) {
            if ($key === 0) {
                $updateDate = date('Y-').$row[1];
            }
            if ($key >= 2 ) {
                $data[] = $row;
            }
        }

        return [
            'datetime' => new DateTime($updateDate),
            'data' => $data,
        ];
    }

    /**
     * @param $tmpDir
     * @return Generator
     * @throws Exception
     */
    public function HSTotalGenerator($tmpDir): Generator {
        $url = 'http://stock.gtimg.cn/data/get_hs_xls.php?id=ranka&type=1&metric=chr';
        $data = $this->httpClient->get($url);
        file_put_contents($tmpDir.'/tmp.xls', $data);
        $reader = IOFactory::createReader('Xls');
        $reader->setReadDataOnly(True);
        $spreadsheet = $reader->load($tmpDir . "/tmp.xls");
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIter = $row->getCellIterator();
            $cellIter->setIterateOnlyExistingCells(False);
            $rowRet = [];
            foreach ($cellIter as $cell) {
                $rowRet[] = $cell->getValue();
            }
            yield $rowRet;
        }
    }
}
