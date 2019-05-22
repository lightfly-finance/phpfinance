<?php

namespace Lightfly\Finance;

interface HttpClientInterface {

    public function get($url, $options = []);
}