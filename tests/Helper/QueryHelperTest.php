<?php

declare(strict_types=1);

namespace Laqu\Test\Helper;

use Carbon\Carbon;
use Laqu\Facades\QueryHelper;
use Laqu\Test\TestCase;

class QueryHelperTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_the_query_after_build()
    {
        $now  = Carbon::now();
        $from = $now->copy()->subDay();
        $to   = $now->copy()->addDay();

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

        $bindings = [
            1,
            2,
            '%Shakespeare',
            $from,
            $to,
        ];

        $actual   = QueryHelper::buildedQuery($query, $bindings);
        $expected = <<<SQL
    select
        *
    from
        authors
    where
        id in (1, 2)
    and
        name like '%Shakespeare'
    and
        updated_at between '{$from}' and '{$to}'
SQL;

        $this->assertSame($expected, $actual);
    }
}
