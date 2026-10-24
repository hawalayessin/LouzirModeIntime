<?php

namespace Webkul\Shipping\Carriers;

class Express extends FlatRate
{
    protected $code = 'express';

    protected $method = 'express_express';
}
