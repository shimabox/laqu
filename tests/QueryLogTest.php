<?php

declare(strict_types=1);

namespace Laqu\Test;

use Laqu\Facades\QueryLog;
use Laqu\Test\Models\Author;

class QueryLogTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_the_results_of_laravel_query_log()
    {
        $actual = QueryLog::getQueryLog(function () {
            Author::find(1);
        });

        $expectedQuery    = 'select * from authors where authors.id = ? limit 1';
        $expectedBindings = [1];

        $this->assertCount(1, $actual);
        $this->assertSame($expectedQuery, $this->removeQuotationMark($actual[0]['query']));
        $this->assertSame($expectedBindings, $actual[0]['bindings']);
        $this->assertTrue($actual[0]['time'] >= 0);
    }

    private function removeQuotationMark(string $query): string
    {
        return str_replace(['`', '"'], '', $query);
    }
}
