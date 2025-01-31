<?php

namespace l3043y\Common\Facades;

use l3043y\Common\Data\ApiResponse;
use Illuminate\Support\Facades\Facade;


class Common extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \l3043y\Common\Common::class;
    }
}
