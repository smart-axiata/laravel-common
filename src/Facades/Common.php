<?php

namespace SmartAxiata\Common\Facades;

use SmartAxiata\Common\Data\ApiResponse;
use Illuminate\Support\Facades\Facade;


class Common extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SmartAxiata\Common\Common::class;
    }
}
