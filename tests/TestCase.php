<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use LaravelQueryAssertion\Facades\LaravelQueryHelper as LaravelQueryHelperFacade;
use LaravelQueryAssertion\Facades\LaravelQueryLog as LaravelQueryLogFacade;
use LaravelQueryAssertion\LaravelQueryAssertionServiceProvider;
use LaravelQueryAssertion\Test\Models\Author;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'LaravelQueryHelper' => LaravelQueryHelperFacade::class,
            'LaravelQueryLog'    => LaravelQueryLogFacade::class,
        ];
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelQueryAssertionServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $this->createAuthors($schema);
    }

    protected function createAuthors(Builder $schema)
    {
        $schema->create('authors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->timestamps();
        });

        Author::create(['name' => 'William Shakespeare']);
    }
}
