<?php

declare(strict_types=1);

namespace Laqu;

use Laqu\Contracts\SqlFormatter as SqlFormatterContract;
use Laqu\Facades\QueryLog;

class QueryHelper
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
        $queryLog = QueryLog::getQueryLog($queryCaller);
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
     * @param  string               $rawSql
     * @param  array                $parameters
     * @return string|string[]|null
     *
     * @see https://github.com/panique/pdo-debug/blob/master/pdo-debug.php
     */
    private function build(string $rawSql, array $parameters)
    {
        $keys   = [];
        $values = [];

        $adjustedParameters = $this->adjustParameters($rawSql, $parameters);

        /*
         |----------------------------------------------------------------------
         | Get longest keys first, sot the regex replacement doesn't
         | cut markers (ex : replace ":username" with "'joe'name"
         | if we have a param name :user )
         |----------------------------------------------------------------------
         */
        $isNamedMarkers = false;
        if (count($adjustedParameters) && is_string(key($adjustedParameters))) {
            uksort($adjustedParameters, function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            });
            $isNamedMarkers = true;
        }

        foreach ($adjustedParameters as $key => $value) {
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

    /**
     * Adjust bind parameters.
     *
     * // When using queryBuilder.
     * QueryHelper::buildedQuery(function () use ($query) {
     *     Author::whereRaw('name like :name', ['name' => '%Shakespeare'])->get();
     * });
     *
     * In the above case, parameter will be [0 => "% Shakespeare"] and query of
     * "select * from authors where like like: name" cannot be assembled.
     * Therefore, parameter is adjusted so that ['name' => "% Shakespeare"].
     *
     * @param  string $rawSql
     * @param  array  $parameters
     * @return array
     */
    private function adjustParameters(string $rawSql, array $parameters): array
    {
        $returnParameters = $parameters;
        $index            = 0;
        preg_replace_callback('/\?|:(\w+)/', function ($matches) use (&$returnParameters, &$index) {
            if (
                $matches[0] !== '?'
                && ! isset($returnParameters[$matches[0]])
                && ! isset($returnParameters[$matches[1]])
            ) {
                $value = $returnParameters[$index];
                $returnParameters[$matches[1]] = $value;
                unset($returnParameters[$index]);
            }
            ++$index;
        }, $rawSql);

        return $returnParameters;
    }
}
