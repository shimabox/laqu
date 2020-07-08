<?php

declare(strict_types=1);

namespace Laqu;

use Laqu\Facades\QueryFormatter;
use Laqu\Facades\QueryLog;

trait QueryAssertion
{
    /**
     * If true, leave quotation mark attached to the query returned by `DB::getQueryLog()`.
     *
     * @var bool
     */
    protected $leaveQuotationMarkApplied = false;

    /**
     * Query assertion.
     *
     * @param  callable     $queryCaller      Process to execute the query.
     * @param  string|array $expectedQueries  Expected query. allows string or array.
     * @param  array        $expectedBindings Expected bind value.
     * @return void
     */
    final public function assertQuery(
        callable $queryCaller,
        $expectedQueries,
        array $expectedBindings = []
    ): void {
        $queryLog = QueryLog::getQueryLog($queryCaller);
        $this->assert($queryLog, (array) $expectedQueries, $expectedBindings);
    }

    /**
     * @param  array $queryLog
     * @param  array $expectedQueries
     * @param  array $expectedBindings
     * @return void
     */
    private function assert(
        array $queryLog,
        array $expectedQueries,
        array $expectedBindings
    ): void {
        $expectedBindings = $this->increaseDimensionsIfSingleDimension($expectedBindings);

        foreach ($queryLog as $index => $log) {
            $expectedQuery = QueryFormatter::compress($expectedQueries[$index] ?? '');
            $actualQuery   = QueryFormatter::compress($log['query']);

            $this->assertSame(
                $this->removeQuotationMark($expectedQuery),
                $this->removeQuotationMark($actualQuery)
            );

            $bindings = $expectedBindings[$index] ?? [];
            $this->assertSame(
                $bindings,
                $log['bindings']
            );
        }

        $this->assertCount(count($expectedQueries), $queryLog);
    }

    /**
     * If it is unidimensional, make it two-dimensional.
     *
     * @param  array $expectedBindings
     * @return array
     */
    private function increaseDimensionsIfSingleDimension(
        array $expectedBindings
    ): array {
        if (empty($expectedBindings)) {
            return $expectedBindings;
        }

        if (isset($expectedBindings[0]) && is_array($expectedBindings[0])) {
            return $expectedBindings;
        }

        return [$expectedBindings];
    }

    /**
     * Remove quotation mark from query.
     *
     * The query returned from `DB::getQueryLog()` returns column names and table names
     * with quotation mark added.
     * e.g. 'select * from "users" where "users"."id" = ?'
     *
     * In that case, queries written with expected values will have to follow it.
     * It is troublesome to write the expected value query in quotation mark, so I will delete it.
     *
     * However, the following properties are prepared in case of using ` or " in a like statement.
     * `leaveQuotationMarkApplied`
     * If you set `leaveQuotationMarkApplied` to true, quotation mark will not be removed.
     *
     * @param  string $query
     * @return string
     */
    private function removeQuotationMark(string $query): string
    {
        if ($this->leaveQuotationMarkApplied === true) {
            return $query;
        }

        return str_replace(['`', '"'], '', $query);
    }
}
