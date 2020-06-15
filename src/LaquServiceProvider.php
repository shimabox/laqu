<?php

declare(strict_types=1);

namespace Laqu;

use Illuminate\Support\ServiceProvider;
use Laqu\Contracts\SqlFormatter as SqlFormatterContract;
use Laqu\SqlFormatter\SqlFormatter;

class LaquServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('laravelQueryLog', function ($app) {
            return $app->make(LaravelQueryLog::class);
        });
    }

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
    }
}
