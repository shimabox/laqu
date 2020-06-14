<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getQueryLog(callable $queryCaller)
 *
 * @see \LaravelQueryAssertion\LaravelQueryLog
 */
class LaravelQueryLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravelQueryLog';
    }
}
