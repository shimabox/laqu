<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test;

use LaravelQueryAssertion\Facades\LaravelQueryHelper;
use LaravelQueryAssertion\Test\Models\Author;

class LaravelQueryHelperTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_return_a_query_after_the_build()
    {
        $actual = LaravelQueryHelper::buildedQuery(function () {
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
        $actual = LaravelQueryHelper::buildedQuery(function () {
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
    public function it_returns_blank_if_passed_an_empty_closure()
    {
        $actual = LaravelQueryHelper::buildedQuery(function () {});

        $this->assertCount(0, $actual);
        $this->assertEmpty($actual);
    }

    private function removeQuotationMark(string $query): string
    {
        return str_replace(['`', '"'], '', $query);
    }
}
