<?php

declare(strict_types=1);

namespace Laqu\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Laqu\LaquServiceProvider;
use Laqu\Test\Models\Author;
use Laqu\Test\Models\Book;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
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
            LaquServiceProvider::class,
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
        $this->createBooks($schema);
    }

    protected function createAuthors(Builder $schema)
    {
        $schema->create('authors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->timestamps();
        });

        Author::create(['name' => 'William Shakespeare']);
        Author::create(['name' => 'J. K. Rowling']);
    }

    protected function createBooks(Builder $schema)
    {
        $schema->create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->integer('author_id');
            $table->timestamps();
        });

        Book::create(['name' => 'Hamlet', 'author_id' => 1]);
        Book::create(['name' => 'Romeo and Juliet', 'author_id' => 1]);
        Book::create(['name' => 'Harry Potter', 'author_id' => 2]);
    }
}
