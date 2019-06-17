<?php

namespace spec\Lightfly\Finance\Fixer;

use GuzzleHttp\Client;
use Lightfly\Finance\Fixer\Fixer;
use Lightfly\Finance\HttpClient;
use PhpSpec\ObjectBehavior;

class FixerSpec extends ObjectBehavior
{

    public function let()
    {
        $apiKey = getenv('FIXER_API_KEY');
        $client = new HttpClient(new Client());
        $this->beConstructedWith($client, $apiKey);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Fixer::class);
    }

    function it_get_all()
    {
        $this->all()->shouldBeArray();
    }
}