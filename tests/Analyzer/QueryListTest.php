<?php

declare(strict_types=1);

namespace Laqu\Test\Analyzer;

use InvalidArgumentException;
use Laqu\Analyzer\Query;
use Laqu\Analyzer\QueryList;
use Laqu\Test\TestCase;
use stdClass;

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

    /**
     * @test
     */
    public function it_can_be_added_QueryList()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0013, '');
        $query3 = new Query('', [], 0.0011, '');

        $queryList = new QueryList([$query1, $query2, $query3]);

        $query4 = new Query('', [], 0.0009, '');
        $query5 = new Query('', [], 0.0013, '');

        $target = new QueryList([$query4, $query5]);

        $target->addQueryList($queryList);

        $actual   = $target->sortByFast();
        $expected = [$query4, $query3, $query1, $query5, $query2];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_be_added_query()
    {
        $query1 = new Query('', [], 0.0012, '');
        $target = new QueryList([$query1]);

        $query2    = new Query('', [], 0.0009, '');
        $target[1] = $query2;

        $query3   = new Query('', [], 0.0013, '');
        $target[] = $query3;

        $this->assertSame($target[0], $query1);
        $this->assertSame($target[1], $query2);
        $this->assertSame($target[2], $query3);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_nonQuery_instance_is_added()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument must pass a Query.');

        $query1 = new Query('', [], 0.0012, '');
        $target = new QueryList([$query1]);

        $target[1] = new stdClass();
    }

    /**
     * @test
     */
    public function it_is_possible_to_confirm_the_existence_of_the_key()
    {
        $query1 = new Query('', [], 0.0012, '');
        $actual = new QueryList([$query1]);

        $this->assertTrue(isset($actual[0]));
        $this->assertFalse(isset($actual[1]));
    }

    /**
     * @test
     */
    public function it_can_be_removed_query()
    {
        $query1 = new Query('', [], 0.0012, '');
        $query2 = new Query('', [], 0.0009, '');
        $target = new QueryList([$query1, $query2]);

        unset($target[0]);

        $this->assertFalse(isset($target[0]));
        $this->assertTrue(isset($target[1]));
    }
}
