<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test;

use LaravelQueryAssertion\Test\Models\User;

class LaravelQueryAssertionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_test()
    {
        User::create(['email' => 'test@test.com']);
        $user = User::find(1);

        $this->assertSame(1, $user->id);
    }
}
