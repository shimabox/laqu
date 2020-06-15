<?php

declare(strict_types=1);

namespace Laqu\Test;

use Laqu\QueryAssertion;
use Laqu\Test\Models\Author;

class QueryAssertionTest extends TestCase
{
    use QueryAssertion;

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
