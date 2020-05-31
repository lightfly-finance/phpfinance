<?php

namespace Lightfly\Finance\Stock;

use Symfony\Component\DomCrawler\Crawler;
use function iter\toArray;

/**
 * Trait RankTrait
 * @package Lightfly\Finance\Stock
 */
trait RankTrait
{
    static $PERANK_URL = 'http://money.finance.sina.com.cn/quotes_service/api/jsonp_v2.php';

    static $ROERANK_URL = 'http://vip.stock.finance.sina.com.cn/q/go.php/vFinanceAnalyze/kind/mainindex/index.phtml';

    /**
     * 市盈率排行
     * 数据页面： http://vip.stock.finance.sina.com.cn/datacenter/hqstat.html#sylv
     * @param int $perPage
     * @return array
     */
    public function PERank($perPage = 400)
    {
        return toArray($this->generatePERank($perPage));
    }

    /**
     * @param int $perPage
     * @return \Generator
     */
    public function PERankIter($perPage = 400)
    {
        return $this->generatePERank($perPage);
    }

    /**
     * @param int $perPage
     * @return \Generator
     */
    private function generatePERank($perPage = 400)
    {
        $page = 1;
        while (true) {
            $randStr = bin2hex(openssl_random_pseudo_bytes(6));
            $params = [
                'page' => $page,
                'node' => 'hs_a',
                'sort' => 'per_d',
                'asc' => 0,
                'num' => $perPage,
            ];
            $url = self::$PERANK_URL."/IO.XSRV2.CallbackList['$randStr']/Market_Center.getHQNodeDataNew?".http_build_query($params);
            $res = $this->httpClient->get($url);
            $data = mb_convert_encoding($res, 'UTF-8', 'GBK');
            $pattern = '/\]\((.*)\);/';
            $match = preg_match_all($pattern, $data, $matches);
            if (!$match) {
                break;
            }

            $rxt = $matches[1][0];
            if (empty($rxt) || $rxt == 'null') {
                break;
            }

            $result = json_decode($rxt, true);
            foreach ($result as $row) {
                yield $row;
            }

            $page += 1;
        }
    }


    /**
     * 数据页面： http://vip.stock.finance.sina.com.cn/q/go.php/vFinanceAnalyze/kind/mainindex/index.phtml
     * @param int $perPage
     * @return array
     */
    public function ROERank($perPage = 300)
    {
        return toArray($this->generateROE($perPage));
    }

    /**
     * @param int $perPage
     * @return \Generator
     */
    public function ROERankIter($perPage = 300)
    {
        return $this->generateROE($perPage);
    }

    /**
     * @param $perPage
     * @return \Generator
     */
    private function generateROE($perPage)
    {
        $page = 1;
        while (true) {
            $HTML = $this->httpClient->get(self::$ROERANK_URL.'?p='.$page.'&order=code|2&num='.$perPage);

            $crawler = new Crawler($HTML);
            $table = $crawler->filter('#dataTable tr')->each(function (Crawler $node) {
                return $node->filter('td')->each(function (Crawler $elem) {
                    return $elem->text();
                });
            });
            // 没有数据，只有标题
            if (count($table) <= 1) {
                break;
            }

            // 去掉标题
            if ($page > 1) {
                array_shift($table);
            }

            foreach ($table as $item) {
                yield [$item[0], $item[1], $item[2], $item[5], $item[7], $item[10]];
            }

            $page += 1;
        }

    }

}
