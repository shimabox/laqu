<?php

declare(strict_types=1);

namespace LaravelQueryAssertion;

use Illuminate\Support\Facades\DB;
use LaravelQueryAssertion\Contracts\SqlFormatter as SqlFormatterContract;

class LaravelQueryHelper
{
    /**
     * @var SqlFormatterContract
     */
    private $formatter;

    public function __construct(SqlFormatterContract $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Query result.
     *
     * @param  callable $queryCaller Process to execute the query.
     * @return array
     */
    public function queryResult(callable $queryCaller): array
    {
        return $this->getQueryLog($queryCaller);
    }

    /**
     * Format the whitespace in a SQL string to make it easier to read.
     *
     * @param  string $string       The SQL string
     * @param  string $indentString
     * @return string The SQL string with HTML styles and formatting wrapped in a <pre> tag
     */
    public function format(string $string, string $indentString = '  '): string
    {
        return $this->formatter->format($string, $indentString);
    }

    /**
     * Add syntax highlighting to a SQL string
     *
     * @param  string $string The SQL string
     * @return string The SQL string with HTML styles applied
     */
    public function highlight(string $string): string
    {
        return $this->formatter->highlight($string);
    }

    /**
     * Compress a query by collapsing white space and removing comments
     *
     * @param  string $string The SQL string
     * @return string The SQL string without comments
     */
    public function compress(string $string): string
    {
        return $this->formatter->compress($string);
    }

    /**
     * Get the connection query log.
     *
     * @param  callable $queryCaller
     * @return array
     */
    private function getQueryLog(callable $queryCaller): array
    {
        DB::enableQueryLog();

        $queryCaller();

        $queryResult = DB::getQueryLog();

        DB::disableQueryLog();

        return $queryResult;
    }
}
