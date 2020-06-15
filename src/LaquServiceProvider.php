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
        $this->app->singleton('queryLog', function ($app) {
            return $app->make(QueryLog::class);
        });
    }

    public function register()
    {
        $this->app->bind(SqlFormatterContract::class, function ($app, $param) {
            return new SqlFormatter($param[0] ?? null);
        });

        $this->app->singleton('queryHelper', function ($app) {
            return $app->make(
                QueryHelper::class,
                [SqlFormatterContract::class]
            );
        });
    }
}
