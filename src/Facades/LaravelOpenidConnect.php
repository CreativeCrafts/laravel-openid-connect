<?php

namespace CreativeCrafts\LaravelOpenidConnect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CreativeCrafts\LaravelOpenidConnect\LaravelOpenidConnect
 */
class LaravelOpenidConnect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CreativeCrafts\LaravelOpenidConnect\LaravelOpenidConnect::class;
    }
}
