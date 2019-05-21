<?php

namespace spec\Monster\Finance\Fund;

use Monster\Finance\Fund\Fund;
use Monster\Finance\HttpClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FundSpec extends ObjectBehavior
{
    public function let()
    {
        $client = new HttpClient();
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Fund::class);
    }

    function it_fetch_fund_list()
    {
        $this->get('000001', '2019-05-01', '2019-05-16')->shouldBeArray();
    }

    public function it_fetch_internet_banking()
    {
        $this->internetBanking()->shouldBeArray();
    }
}
