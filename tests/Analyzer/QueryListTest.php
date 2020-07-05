<?php

declare(strict_types=1);

namespace Laqu\Test\Analyzer;

use Laqu\Analyzer\Query;
use Laqu\Analyzer\QueryList;
use Laqu\Test\TestCase;

class QueryListTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_extract_the_fastest_query()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0013, '');
        $query3 = new Query('', [], 0.0011, '');

        $target = new QueryList([$query1, $query2, $query3]);

        $actual = $target->extractFastestQuery();

        $this->assertEquals($query3, $actual);
    }

    /**
     * @test
     */
    public function it_can_extract_the_slowest_query()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0013, '');
        $query3 = new Query('', [], 0.0011, '');

        $target = new QueryList([$query1, $query2, $query3]);

        $actual = $target->extractSlowestQuery();

        $this->assertEquals($query2, $actual);
    }

    /**
     * @test
     */
    public function it_can_sort_them_in_ascending_order_of_execution_time()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0013, '');
        $query3 = new Query('', [], 0.0011, '');

        $target = new QueryList([$query1, $query2, $query3]);

        $actual   = $target->sortByFast();
        $expected = [$query3, $query1, $query2];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_sort_them_in_descending_order_of_execution_time()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0013, '');
        $query3 = new Query('', [], 0.0011, '');

        $target = new QueryList([$query1, $query2, $query3]);

        $actual   = $target->sortBySlow();
        $expected = [$query2, $query1, $query3];

        $this->assertEquals($expected, $actual);
    }
}
