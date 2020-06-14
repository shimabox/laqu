<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array queryResult(callable $queryCaller)
 * @method static array buildedQuery(callable $queryCaller)
 * @method static string format(string $string, string $indentString = '  ')
 * @method static string highlight(string $string)
 * @method static string compress(string $string)
 *
 * @see \LaravelQueryAssertion\LaravelQueryHelper
 */
class LaravelQueryHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravelQueryHelper';
    }
}
