<?php

declare(strict_types=1);

namespace Laqu\Test;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laqu\QueryAssertion;
use Laqu\Test\Models\Author;
use Laqu\Test\Models\Book;

class QueryAssertionTest extends TestCase
{
    use QueryAssertion;

    /**
     * @test
     */
    public function it_can_assert_a_simple_query()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        authors.id in (?, ?)
SQL;

        $expectedBindings = [1, 2];

        $this->assertQuery(
            function () {
                Author::find([1, 2]);
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_multiple_queries()
    {
        $expectedQuery_1 = 'select * from authors where name like :name';
        $expectedQuery_2 = 'select * from authors where id in (?)';
        $expectedQuery_3 = 'delete from authors where id = ?';
        $expectedQueries = [
            $expectedQuery_1,
            $expectedQuery_2,
            $expectedQuery_3,
        ];

        $expectedBindings = [
            ['name' => '%Shakespeare'],
            ['1'],
            ['1'],
        ];

        $this->assertQuery(
            function () {
                $query  = 'select * from authors where name like :name';
                $author = DB::select($query, ['name' => '%Shakespeare']);
                Author::destroy($author[0]->id);
            },
            $expectedQueries,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_question_mark_parameters_in_QueryBuilder()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        id = ?
SQL;

        $expectedBindings = [1];

        $this->assertQuery(
            function () {
                Author::where('id', '=', 1)->get();
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_question_mark_parameters_in_raw_sql()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        id = ?
SQL;

        $expectedBindings = [1];

        $this->assertQuery(
            function () {
                $query = 'select * from authors where id = ?';
                DB::select($query, [1]);
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_QueryBuilder()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        name like :name
SQL;

        $expectedBindings = ['%Shakespeare'];

        $this->assertQuery(
            function () {
                Author::whereRaw(
                    'name like :name',
                    ['name' => '%Shakespeare']
                )->get();
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_in_raw_sql()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        name like :name
SQL;

        $expectedBindings = ['name' => '%Shakespeare'];

        $this->assertQuery(
            function () {
                $query = 'select * from authors where name like :name';
                DB::select($query, ['name' => '%Shakespeare']);
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_for_named_parameter_and_question_mark_parameter_in_QueryBuilder()
    {
        $now  = Carbon::now();
        $from = $now->copy()->subDay();
        $to   = $now->copy()->addDay();

        $expectedQuery = <<<SQL
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

        $expectedBindings = [
            1,
            2,
            '%Shakespeare',
            $from,
            $to,
        ];

        $this->assertQuery(
            function () use ($from, $to) {
                Author::whereIn('id', [1, 2])
                    ->whereRaw('name like :name', ['name' => '%Shakespeare'])
                    ->whereBetween('updated_at', [$from, $to])
                    ->get();
            },
            $expectedQuery,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_leave_quotation_marks_and_assert()
    {
        $this->leaveQuotationMarkApplied = true;

        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        name like '%''"%'
SQL;

        $expectedBindings = [];

        $this->assertQuery(
            function () {
                $query = "select * from authors where name like '%''\"%'";
                DB::select($query);
            },
            $expectedQuery,
            $expectedBindings
        );

        $this->leaveQuotationMarkApplied = false;
    }

    /**
     * @test
     */
    public function it_can_assert_for_hasMany()
    {
        $expectedQuery_1 = 'select * from authors';
        $expectedQuery_2 = 'select * from books where books.author_id = ? and books.author_id is not null';

        $expectedQueries = [
            $expectedQuery_1,
            $expectedQuery_2,
            $expectedQuery_2,
        ];

        $expectedBindings = [
            [],
            [1],
            [2],
        ];

        $this->assertQuery(
            function () {
                $authors = Author::all();
                foreach ($authors as $author) {
                    $author->books()->get();
                }
            },
            $expectedQueries,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_for_belongsTo()
    {
        $expectedQuery_1 = 'select * from books where books.id = ? limit 1';
        $expectedQuery_2 = 'select * from authors where authors.id = ?';

        $expectedQueries = [
            $expectedQuery_1,
            $expectedQuery_2,
        ];

        $expectedBindings = [
            [1],
            ['1'],
        ];

        $this->assertQuery(
            function () {
                Book::find(1)->author()->get();
            },
            $expectedQueries,
            $expectedBindings
        );
    }

    /**
     * @test
     */
    public function it_can_assert_for_EagerLoad()
    {
        $expectedQuery_1 = 'select * from authors';
        $expectedQuery_2 = 'select * from books where books.author_id in (1, 2)';

        $expectedQueries = [
            $expectedQuery_1,
            $expectedQuery_2,
        ];

        $expectedBindings = [
            [],
            [],
        ];

        $this->assertQuery(
            function () {
                Author::with('books')->get();
            },
            $expectedQueries,
            $expectedBindings
        );
    }
}
