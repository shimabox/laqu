<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test;

use LaravelQueryAssertion\LaravelQueryAssertion;
use LaravelQueryAssertion\Test\Models\Author;

class LaravelQueryAssertionTest extends TestCase
{
    use LaravelQueryAssertion;

    /**
     * @test
     */
    public function it_can_test()
    {
        $expectedQuery = <<<SQL
    select
        *
    from
        authors
    where
        authors.id = ?
    limit 1
SQL;

        $expectedBindings = [1];

        $this->assertQuery(
            function () {
                Author::find(1);
            },
            $expectedQuery,
            $expectedBindings
        );
    }
}
