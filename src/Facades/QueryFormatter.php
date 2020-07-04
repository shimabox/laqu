<?php

declare(strict_types=1);

namespace Laqu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string format(string $string, string $indentString = '  ')
 * @method static string highlight(string $string)
 * @method static string compress(string $string)
 *
 * @see \Laqu\Formatter\QueryFormatter
 */
class QueryFormatter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queryFormatter';
    }
}
