<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getQueryLog(callable $queryCaller)
 *
 * @see \Laqu\QueryLog
 */
class QueryLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queryLog';
    }
}
