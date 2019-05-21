<?php

namespace Lightfly\Finance\Stock;


use Generator;
use function iter\toArray;

trait StockIndicatorTrait
{

    static $INDICATOR_API = 'http://quotes.money.163.com/service';

    /**
     * 主要财务指标
     * 数据源： http://quotes.money.163.com/f10/zycwzb_002142.html
     *
     * @param $symbol  string 不带前缀sh or sz
     * @return array
     */
    public function mainFinancialIndicators($symbol)
    {
        return toArray($this->getIndicators($symbol));
    }

    /**
     * @param $symbol
     * @return Generator
     */
    public function mainFinancialIndicatorsIter($symbol)
    {
        return $this->getIndicators($symbol);
    }

    /**
     * 盈利能力指标
     * 数据源： http://quotes.money.163.com/f10/zycwzb_002142.html
     *
     * @param $symbol string
     * @return array
     */
    public function profitability(string $symbol)
    {
        return toArray($this->getIndicators($symbol, 'ylnl'));
    }

    /**
     * @param string $symbol
     * @return Generator
     */
    public function profitabilityIter(string $symbol)
    {
        return $this->getIndicators($symbol, 'ylnl');
    }

    /**
     * 偿债能力
     *
     * @param string $symbol
     * @return array
     */
    public function solvency(string $symbol)
    {
        return toArray($this->getIndicators($symbol, 'chnl'));
    }

    /**
     * @param string $symbol
     * @return Generator
     */
    public function solvencyIter(string $symbol)
    {
        return $this->getIndicators($symbol, 'chnl');
    }

    /**
     * 成长能力
     *
     * @param string $symbol
     * @return array
     */
    public function growthAbility(string $symbol)
    {
        return toArray($this->getIndicators($symbol, 'cznl'));
    }

    /**
     * @param string $symbol
     * @return Generator
     */
    public function growthAbilityIter(string $symbol)
    {
        return $this->getIndicators($symbol, 'cznl');
    }

    /**
     * 获取指标数据
     * @param string $symbol
     * @param string|null $indicator
     * @return Generator
     */
    private function getIndicators(string $symbol, string $indicator = null)
    {
        $segmentPath = '/zycwzb_'.$symbol.'.html?type=report';
        if ($indicator) {
            $segmentPath .= "&part=$indicator";
        }
        $res = $this->httpClient->get(self::$INDICATOR_API.$segmentPath);
        $res = mb_convert_encoding($res, 'UTF-8', 'GBK');

        return $this->parseCSV($res);
    }
    /**
     * @param string $data
     * @return Generator
     */
    private function parseCSV(string $data)
    {
        $data = explode("\r\n", $data);
        foreach ($data as $row) {
            if (!empty(trim($row))) {
                yield explode(',', $row);
            }
        }
    }
}