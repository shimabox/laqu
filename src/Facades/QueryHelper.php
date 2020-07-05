<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string buildedQuery(string $rawSql, array $parameters = [])
 *
 * @see \Laqu\Helper\QueryHelper
 */
class QueryHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queryHelper';
    }
}
