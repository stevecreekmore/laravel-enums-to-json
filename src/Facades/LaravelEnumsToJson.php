<?php

namespace Creekmore108\LaravelEnumsToJson\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Creekmore108\LaravelEnumsToJson\LaravelEnumsToJson
 */
class LaravelEnumsToJson extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Creekmore108\LaravelEnumsToJson\LaravelEnumsToJson::class;
    }
}
