<?php
namespace Lightfly\Finance\Fund;


use DateTime;
use Generator;
use function iter\map;
use function iter\toArray;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Lightfly\Finance\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class Fund
{

    const FUND_INFO_API = 'http://quotes.money.163.com/fund';
    const FUND_ALL = 'http://vip.stock.finance.sina.com.cn/fund_center/data/jsonp.php';
    const INTERNET_BANKING_API = 'http://quotes.money.163.com/fn/service/internetBanking.php';
    const API = 'http://stock.finance.sina.com.cn/fundInfo/api/openapi.php/CaihuiFundInfoService.getNav';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Fund constructor.
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
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
    public function get($symbol, $dateFrom, $dateTo): array
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
    private function getFundPage($url): Generator
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
    public function internetBanking(): array
    {
        $queryString = http_build_query([
            'sort' => 'CUR4',
            'order' => 'asc',
            'type' => 'FG',
            'count' => 50,
        ]);

        $queryString .= '&fields=NO,LI_CAI_MING_CHENG,FA_SHOU_SHANG,SYMBOL,SNAME,CUR4,CURNAV_001,CURNAV_010,CURNAV_011,OFPROFILE8,PUBLISHDATE';
        $url = self::INTERNET_BANKING_API.'?'.$queryString;
        $res = $this->httpClient->get($url);
        $data = json_decode($res, true);

        $result = map(function ($item) {

            return [
                '理财名称' => $item['LI_CAI_MING_CHENG'],
                '发售商' => $item['FA_SHOU_SHANG'],
                '代码' => $item['SYMBOL'],
                '序号' => $item['NO'],
                '基金名称' => $item['SNAME'],
                '万份收益' => $item['CUR4'],
                '七日年化收益率' => $item['CURNAV_001'],
                '三月收益率' => $item['CURNAV_010'],
                '半年收益率' => $item['CURNAV_011'],
                '发布日期' => $item['PUBLISHDATE'],
                '成立日期' => $item['OFPROFILE8'],
            ];
        }, $data['list']);

        return toArray($result);
    }

    /**
     * 基金基本信息
     * 数据源： http://quotes.money.163.com/fund/jjzl_150199.html
     * @param string $symbol
     * @return array
     */
    public function basicInfo(string $symbol): array
    {
        $url = self::FUND_INFO_API . "/jjzl_$symbol.html";

        $html = $this->httpClient->get($url);

        $crawler = new Crawler($html);
        return $crawler->filter('.fn_cm_table tr')->each(function (Crawler $node, $i) {

            $label = $node->filter('th')->text();
            $value = $node->filter('td')->text();

            return [
                $label => $value,
            ];
        });
    }

    /**
     * 基金重仓持股
     * 数据源：http://quotes.money.163.com/fund/cgmx_150199.html
     * @param string $symbol
     * @return array
     */
    public function stocksHolding(string $symbol): array
    {
        $url = self::FUND_INFO_API . "/cgmx_$symbol.html";
        $html = $this->httpClient->get($url);
        $crawler = new Crawler($html);
        $header = ["股票名称", "持有量（股）", "市值（元）", "占净值比"];

        $data = $crawler->filter('.fn_fund_rank')
                        ->first()
                        ->filter('tbody tr')
                        ->each(function (Crawler $node, $i) {

            $row = $node->filter('td')->each(function (Crawler $node, $j) {
                return $node->text();
            });

            return $row;
        });

        array_unshift($data, $header);

        return $data;
    }

    /**
     * 数据：http://vip.stock.finance.sina.com.cn/fund_center/index.html#jzkfall
     * @param $page
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function all($page = 1, $limit = 80): array
    {
        $params = [
            'page' => $page,
            'num' => $limit,
            'sort' => 'form_year',
            'asc' => 0,
        ];

        $qs = http_build_query($params);

        $random = mt_rand(100000, 999999);
        $url = self::FUND_ALL."/IO.XSRV2.CallbackList['$random']/NetValueReturn_Service.NetValueReturnOpen?".$qs;
        $res = $this->httpClient->get($url);

        $pattern = '/\]\((.*)\)/';
        $match = preg_match($pattern, $res, $matches);
        if ($match) {
            return json_decode($matches[1], true);
        } else {
            throw new \Exception("data parse error.");
        }
    }

    /**
     * url: http://stock.gtimg.cn/data/get_hs_xls.php?id=rankfund&type=1&metric=chr
     * ETF 或者 LOF 上市交易的基金
     * @param string $tmpDir
     * @return array
     * @throws \Exception
     */
    public function stockFund($tmpDir = '.'): array
    {
        $updateDate = '';
        $data = [];
        foreach ($this->totalStockFundGenerator($tmpDir) as $key => $row) {
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
     *
     * @param string $tmpDir
     * @return Generator
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function totalStockFundGenerator(string $tmpDir): Generator
    {

        $query = [
            'id' => 'rankfund',
            'type' => 1,
            'metric' => 'chr',
        ];

        $uri = 'http://stock.gtimg.cn/data/get_hs_xls.php';
        $url = $uri .'?'. http_build_query($query);

        $data = $this->httpClient->get($url);
        file_put_contents($tmpDir . '/tmp_fund.xls', $data);

        $reader = IOFactory::createReader('Xls');
        $reader->setReadDataOnly(True);
        $spreadsheet = $reader->load($tmpDir . "/tmp_fund.xls");
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
