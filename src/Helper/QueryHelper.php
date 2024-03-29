<?php

declare(strict_types=1);

namespace Laqu\Helper;

use Carbon\Carbon;

class QueryHelper
{
    /**
     * Returns the emulated SQL string
     *
     * @param  string       $rawSql
     * @param  array<mixed> $parameters
     * @return string
     *
     * @see https://github.com/panique/pdo-debug/blob/master/pdo-debug.php
     */
    public function buildedQuery(string $rawSql, array $parameters = []): string
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
            } elseif ($value instanceof Carbon) {
                $values[] = "'" . (string) $value . "'";
            }
        }

        if ($isNamedMarkers) {
            return (string) preg_replace($keys, $values, $rawSql);
        } else {
            return (string) preg_replace($keys, $values, $rawSql, 1, $count);
        }
    }

    /**
     * Adjust bind parameters.
     *
     * // When using queryBuilder.
     * QueryHelper::buildedQuery(
     *     fn () => Author::whereRaw('name like :name', ['name' => '%Shakespeare'])->get()
     * );
     *
     * In the above case, parameter will be [0 => "%Shakespeare"] and query of
     * "select * from authors where like like: name" cannot be assembled.
     * Therefore, parameter is adjusted so that ['name' => "%Shakespeare"].
     *
     * @param  string       $rawSql
     * @param  array<mixed> $parameters
     * @return array<mixed>
     */
    private function adjustParameters(string $rawSql, array $parameters): array
    {
        $returnParameters = $parameters;
        $index            = 0;
        preg_replace_callback('/\?|:(\w+)/', function ($matches) use (&$returnParameters, &$index): string {
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
            return '';
        }, $rawSql);

        return $returnParameters;
    }
}
