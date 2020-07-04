<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array buildedQuery(callable $queryCaller)
 *
 * @see \Laqu\QueryHelper
 */
class QueryHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queryHelper';
    }
}
