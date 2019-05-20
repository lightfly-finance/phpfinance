<?php

namespace spec\Monster\Finance\Stock;

use Monster\Finance\HttpClient;
use Monster\Finance\Stock\Stock;
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
        $this->dailyHistory('600365', '2019-05-01', '2019-05-17')
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
}
