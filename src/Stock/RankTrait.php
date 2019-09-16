<?php

namespace Lightfly\Finance\Stock;

use function iter\toArray;

trait RankTrait
{
    static $PERANK_URL = 'http://money.finance.sina.com.cn/quotes_service/api/jsonp_v2.php';

    /**
     * 市盈率排行
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
            $pattern = "/{([^{}]+)}/";
            $match = preg_match_all($pattern, $data, $matches);
            if (!$match) {
                break;
            }

            foreach ($matches[1] as $row) {
                $item = explode(',', $row);

                $result = [];

                foreach ($item as $kv) {
                    list($key, $value) = explode(':', $kv);
                    $result[$key] = trim($value, '"');
                }

                yield $result;
            }

            $page += 1;
        }
    }

}
