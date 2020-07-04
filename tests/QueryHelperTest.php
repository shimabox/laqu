<?php

declare(strict_types=1);

namespace Laqu\Test;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laqu\Facades\QueryFormatter;
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
        $buildedQuery = QueryHelper::buildedQuery(function () {
            Author::where('id', '=', 1)->get();
        });

        $actual   = QueryFormatter::compress($buildedQuery[0]);
        $expected = 'select * from authors where id = 1';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_question_mark_parameters_in_raw_sql()
    {
        $buildedQuery = QueryHelper::buildedQuery(function () {
            $query = 'select * from authors where id = ?';
            DB::select($query, [1]);
        });

        $actual   = QueryFormatter::compress($buildedQuery[0]);
        $expected = 'select * from authors where id = 1';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_QueryBuilder()
    {
        $buildedQuery = QueryHelper::buildedQuery(function () {
            Author::whereRaw(
                'name like :name',
                ['name' => '%Shakespeare']
            )->get();
        });

        $actual   = QueryFormatter::compress($buildedQuery[0]);
        $expected = 'select * from authors where name like \'%Shakespeare\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_raw_sql()
    {
        $buildedQuery = QueryHelper::buildedQuery(function () {
            $query = 'select * from authors where name like :name';
            DB::select($query, ['name' => '%Shakespeare']);
        });

        $actual   = QueryFormatter::compress($buildedQuery[0]);
        $expected = 'select * from authors where name like \'%Shakespeare\'';

        $this->assertSame($expected, $this->removeQuotationMark($actual));
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_and_question_mark_parameter_in_QueryBuilder()
    {
        $now  = Carbon::now();
        $from = $now->copy()->subDay();
        $to   = $now->copy()->addDay();

        $buildedQuery = QueryHelper::buildedQuery(function () use ($from, $to) {
            Author::whereIn('id', [1, 2])
                ->whereRaw('name like :name', ['name' => '%Shakespeare'])
                ->whereBetween('updated_at', [$from, $to])
                ->get();
        });

        $actual   = QueryFormatter::compress($buildedQuery[0]);
        $expected = 'select * from authors where id in (1, 2) and name like \'%Shakespeare\' and updated_at between \'' . $from . '\' and \'' . $to . '\'';

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
