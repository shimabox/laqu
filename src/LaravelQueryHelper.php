<?php

declare(strict_types=1);

namespace Laqu;

use Laqu\Contracts\SqlFormatter as SqlFormatterContract;
use Laqu\Facades\LaravelQueryLog;

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
     * Get the SQL representation of the query after it has been build.
     *
     * @param  callable $queryCaller Process to execute the query.
     * @return array
     */
    public function buildedQuery($queryCaller): array
    {
        $queryLog = LaravelQueryLog::getQueryLog($queryCaller);
        return array_map(function ($query) {
            return $this->build($query['query'], $query['bindings']);
        }, $queryLog);
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
     * Returns the emulated SQL string
     *
     * @param $rawSql
     * @param $parameters
     * @return string|string[]|null
     *
     * @see https://github.com/panique/pdo-debug/blob/master/pdo-debug.php
     */
    private function build($rawSql, $parameters)
    {
        $keys   = [];
        $values = [];

        /*
         |----------------------------------------------------------------------
         | Get longest keys first, sot the regex replacement doesn't
         | cut markers (ex : replace ":username" with "'joe'name"
         | if we have a param name :user )
         |----------------------------------------------------------------------
         */
        $isNamedMarkers = false;
        if (count($parameters) && is_string(key($parameters))) {
            uksort($parameters, function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            });
            $isNamedMarkers = true;
        }

        foreach ($parameters as $key => $value) {
            // check if named parameters (':param') or anonymous parameters ('?') are used
            if (is_string($key)) {
                $keys[] = '/:' . ltrim($key, ':') . '/';
            } else {
                $keys[] = '/[?]/';
            }

            // bring parameter into human-readable format
            if (is_string($value)) {
                $values[] = "'" . addslashes($value) . "'";
            } elseif (is_int($value)) {
                $values[] = (string) $value;
            } elseif (is_float($value)) {
                $values[] = (string) $value;
            } elseif (is_array($value)) {
                $values[] = implode(',', $value);
            } elseif (is_null($value)) {
                $values[] = 'NULL';
            }
        }

        if ($isNamedMarkers) {
            return preg_replace($keys, $values, $rawSql);
        } else {
            return preg_replace($keys, $values, $rawSql, 1, $count);
        }
    }
}
