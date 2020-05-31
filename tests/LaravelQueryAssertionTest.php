<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test;

use LaravelQueryAssertion\LaravelQueryAssertion;
use LaravelQueryAssertion\Test\Models\User;

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
        users
    where
        users.id = ?
    limit 1
SQL;

        $expectedBindings = [1];

        $this->assertQuery(
            function () {
                $user = User::find(1);
            },
            $expectedQuery,
            $expectedBindings
        );
    }
}
