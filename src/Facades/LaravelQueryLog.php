<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getQueryLog(callable $queryCaller)
 *
 * @see \Laqu\LaravelQueryLog
 */
class LaravelQueryLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravelQueryLog';
    }
}
