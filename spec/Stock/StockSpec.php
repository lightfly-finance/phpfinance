<?php

namespace spec\Lightfly\Finance\Stock;

use Lightfly\Finance\HttpClient;
use Lightfly\Finance\Stock\Stock;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StockSpec extends ObjectBehavior
{
    public function let()
    {
        $client = new HttpClient();
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Stock::class);
    }

    public function it_fetch_hg_tong_top_10()
    {
        $this->HGTongTop10()->shouldBeArray();
        $this->HGTongTop10()->shouldHaveKey('s_date');
        $this->HGTongTop10()->shouldHaveKey('s_list');
    }

    public function it_fetch_sg_tong_top_10()
    {
        $this->SGTongTop10()->shouldBeArray();
        $this->SGTongTop10()->shouldHaveKey('s_date');
        $this->SGTongTop10()->shouldHaveKey('s_list');
    }

    public function it_fetch_hk_tong_top_10()
    {
        $this->HKTongTop10()->shouldBeArray();
        $this->HKTongTop10()->shouldNotHaveKey('s_date');
        $this->HKTongTop10()->shouldHaveKey('s_list');
    }

    public function it_fetch_stock_index()
    {
        $this->stockIndex()->shouldBeArray();
    }

    public function it_fetch_hs300()
    {
        $this->HS300()->shouldBeArray();
    }

    public function it_fetch_stock_daily_history()
    {
        $this->dailyHistory('sz002142', '2019-05-01', '2019-05-17')
            ->shouldBeArray();
    }

    public function it_fetch_sh_index_component_stocks()
    {
        $this->SHIndexComponentStocks()->shouldBeArray();
    }

    public function it_return_sh_index_component_stocks_iter()
    {
        $this->SHIndexComponentStocksIter()->shouldHaveType(\Generator::class);
    }

    public function it_fetch_sh50_index_component_stocks()
    {
        $this->SH50IndexComponentStocks()->shouldBeArray();
    }

    public function it_fetch_sh_consumption_index_component_stocks()
    {
        $this->SHConsumptionIndexComponentStocks()->shouldBeArray();
    }

    public function it_fetch_sh_medicine_index_component_stocks()
    {
        $this->SHMedicineIndexComponentStocks()->shouldBeArray();
    }

    public function it_fetch_sz_composite_index_component_stocks()
    {
        $this->SZCompositeIndexComponentStocks()->shouldBeArray();
    }

    public function it_return_sz_composite_index_component_stocks_iter()
    {
        $this->SZCompositeIndexComponentStocksIter()->shouldHaveType(\Generator::class);
    }

    public function it_fetch_zz500_index_component_stocks()
    {
        $this->ZZ500IndexComponentStocks()->shouldBeArray();
    }

    public function it_return_zz500_index_component_stocks_iter()
    {
        $this->ZZ500IndexComponentStocksIter()->shouldHaveType(\Generator::class);
    }

    public function it_fetch_sz_index_component_stocks()
    {
        $this->SZIndexComponentStocks()->shouldBeArray();
    }

    public function it_return_sz_index_component_stocks_iter()
    {
        $this->SZIndexComponentStocksIter()->shouldHaveType(\Generator::class);
    }

    public function it_get_stock_suggest()
    {
        $this->suggest("格力")->shouldBeArray();
    }

    public function it_fetch_realtime_stock()
    {
        $this->realTimeStock(['sz002142', 'sh601001', 'sh601003'])->shouldBeArray();
    }

    public function it_fetch_main_financial_indicator()
    {
        $this->mainFinancialIndicators('002142')->shouldBeArray();
    }
    public function it_return_main_financial_indicator_iter()
    {
        $this->mainFinancialIndicatorsIter('002142')->shouldHaveType(\Generator::class);
    }

    public function it_fetch_profitability()
    {
        $this->profitability('002142')->shouldBeArray();
    }

    public function it_return_profitability_iter()
    {
        $this->profitabilityIter('002142')->shouldHaveType(\Generator::class);
    }

    public function it_fetch_solvency()
    {
        $this->solvency('002142')->shouldBeArray();
    }
    public function it_return_solvency_iter()
    {
        $this->solvencyIter('002142')->shouldHaveType(\Generator::class);
    }

    public function it_fetch_growth_ability()
    {
        $this->growthAbility('002142')->shouldBeArray();
    }

    public function it_return_growth_ability_iter()
    {
        $this->growthAbilityIter('002142')->shouldHaveType(\Generator::class);
    }

    public function it_fetch_long_hu_bang()
    {
        $this->longHuBang('2019-05-20', '2019-05-21')->shouldBeArray();
    }
}
