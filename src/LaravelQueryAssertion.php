<?php

declare(strict_types=1);

namespace LaravelQueryAssertion;

use Illuminate\Support\Facades\DB;
use SqlFormatter;

/**
 * Query assertion.
 *
 * <code>
 * use QueryAssertion;
 *
 * // Basic usage.
 * $this->assertQuery(
 *     // Pass the process in which the query will be executed in the closure.
 *     function () {
 *         $this->UserRepository->findById('a123');
 *     },
 *     // Write the expected query.
 *     'select * from user where id = ? and is_active = ?',
 *     // Define the expected bind values as an array.
 *     // (If there is nothing to bind, pass an empty array or do not pass the argument)
 *     [
 *         'a123',
 *         1,
 *     ]
 * );
 *
 * // Assert multiple queries.
 * // Basically, it's a good idea to look at one query in one method,
 * // but there are cases where one method executes multiple queries.
 * // In that case, define the query and bind value as an array pair as shown below.
 * $this->assertQuery(
 *     function () {
 *         // For example, if multiple queries are executed in this process
 *         $this->UserRepository->findAll();
 *     },
 *     // Define an array for each expected query.
 *     [
 *         'select * from user where is_active = ?', // ※1
 *         'select * from admin_user where id = ? and is_active = ?', // ※2
 *         'select * from something', // ※3
 *     ],
 *     // Define the bind values ​​as a two-dimensional array (pass empty array if there is nothing to bind).
 *     [
 *         [ // ※1.
 *             1,
 *         ],
 *         [ // ※2.
 *             'b123',
 *             1,
 *         ],
 *         // ※3 is no bind.
 *         []
 *     ]
 * );
 * </code>
 *
 * @see https://github.com/jdorn/sql-formatter
 */
trait LaravelQueryAssertion
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
        DB::enableQueryLog();

        $queryCaller();

        $queryResult = DB::getQueryLog();

        DB::disableQueryLog();

        $this->assert($queryResult, (array) $expectedQueries, $expectedBindings);
    }

    /**
     * @param  array $queryResult
     * @param  array $expectedQueries
     * @param  array $expectedBindings
     * @return void
     */
    private function assert(
        array $queryResult,
        array $expectedQueries,
        array $expectedBindings
    ): void {
        $expectedBindings = $this->increaseDimensionsIfSingleDimension($expectedBindings);

        foreach ($queryResult as $index => $result) {
            $query       = SqlFormatter::compress($expectedQueries[$index] ?? '');
            $actualQuery = SqlFormatter::compress($result['query']);
            $this->assertSame(
                $this->removeQuotationMark($query),
                $this->removeQuotationMark($actualQuery)
            );

            $bindings = $expectedBindings[$index] ?? [];
            $this->assertSame(
                $bindings,
                $result['bindings']
            );
        }
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

        return is_array($expectedBindings[0])
            ? $expectedBindings
            : [$expectedBindings];
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
