<?php

declare(strict_types=1);

namespace Laqu\Test;

use Illuminate\Support\Facades\DB;
use Laqu\Facades\QueryHelper;
use Laqu\Test\Models\Author;

class QueryHelperTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_return_a_query_after_the_build()
    {
        $actual = QueryHelper::buildedQuery(function () {
            Author::all();
        });

        $expected = 'select * from authors';

        $this->assertCount(1, $actual);
        $this->assertSame($expected, $this->removeQuotationMark($actual[0]));
    }

    /**
     * @test
     */
    public function it_can_return_queries_after_the_build()
    {
        $actual = QueryHelper::buildedQuery(function () {
            $author = Author::find(1);
            $author->delete();
        });

        $expected_1 = 'select * from authors where authors.id = 1 limit 1';
        $expected_2 = 'delete from authors where id = \'1\'';

        $this->assertCount(2, $actual);
        $this->assertSame($expected_1, $this->removeQuotationMark($actual[0]));
        $this->assertSame($expected_2, $this->removeQuotationMark($actual[1]));
    }

    /**
     * @test
     */
    public function it_can_assert_question_mark_parameters_in_QueryBuilder()
    {
        $query = <<<SQL
    select
        *
    from
        authors
    where
        id = ?
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query) {
            Author::where('id', '=', 1)->get();
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where id = 1';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_question_mark_parameters_in_raw_sql()
    {
        $query = <<<SQL
    select
        *
    from
        authors
    where
        id = ?
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query) {
            DB::select($query, [1]);
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where id = 1';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_QueryBuilder()
    {
        $query = <<<SQL
    select
        *
    from
        authors
    where
        name like :name
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query) {
            Author::whereRaw(
                'name like :name',
                ['name' => '%Shakespeare']
            )->get();
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where name like \'%Shakespeare\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_raw_sql()
    {
        $query = <<<SQL
    select
        *
    from
        authors
    where
        name like :name
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query) {
            DB::select($query, ['name' => '%Shakespeare']);
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where name like \'%Shakespeare\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_and_question_mark_parameter_in_QueryBuilder()
    {
        $from = '2020-01-01';
        $to   = '2020-12-31';

        $query = <<<SQL
    select
        *
    from
        authors
    where
        id in (?, ?)
    and
        name like :name
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query, $from, $to) {
            Author::whereIn('id', [1, 2])
                ->whereRaw('name like :name', ['name' => '%Shakespeare'])
                ->whereBetween('updated_at', [$from, $to])
                ->get();
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where id in (1, 2) and name like \'%Shakespeare\' and updated_at between \'2020-01-01\' and \'2020-12-31\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_and_question_mark_parameter_in_raw_sql()
    {
        $from = '2020-01-01';
        $to   = '2020-12-31';

        $query = <<<SQL
    select
        *
    from
        authors
    where
        id in (?, ?)
    and
        name like :name
    and
        updated_at between ? and ?
SQL;

        $buildedQuery = QueryHelper::buildedQuery(function () use ($query, $from, $to) {
            DB::select($query, [1, 2, 'name' => '%Shakespeare', $from, $to]);
        });

        $actual   = QueryHelper::compress($buildedQuery[0]);
        $expected = 'select * from authors where id in (1, 2) and name like \'%Shakespeare\' and updated_at between \'2020-01-01\' and \'2020-12-31\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_returns_blank_if_passed_an_empty_closure()
    {
        $actual = QueryHelper::buildedQuery(function () {
        });

        $this->assertCount(0, $actual);
        $this->assertEmpty($actual);
    }

    private function removeQuotationMark(string $query): string
    {
        return str_replace(['`', '"'], '', $query);
    }
}
