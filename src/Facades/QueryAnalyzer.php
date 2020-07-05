<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static QueryList analyze(callable $queryCaller)
 *
 * @see \Laqu\Analyzer\QueryAnalyzer
 */
class QueryAnalyzer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queryAnalyzer';
    }
}
