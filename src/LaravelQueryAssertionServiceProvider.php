<?php

declare(strict_types=1);

namespace LaravelQueryAssertion;

use Illuminate\Support\ServiceProvider;
use LaravelQueryAssertion\Contracts\SqlFormatter as SqlFormatterContract;
use LaravelQueryAssertion\SqlFormatter\SqlFormatter;

class LaravelQueryAssertionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(SqlFormatterContract::class, function ($app, $param) {
            return new SqlFormatter($param[0] ?? null);
        });

        $this->app->singleton('laravelQueryHelper', function ($app) {
            return $app->make(
                LaravelQueryHelper::class,
                [SqlFormatterContract::class]
            );
        });

        $this->app->singleton('laravelQueryLog', function ($app) {
            return $app->make(LaravelQueryLog::class);
        });
    }
}
