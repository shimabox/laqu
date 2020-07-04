<?php

declare(strict_types=1);

namespace Laqu;

use Doctrine\SqlFormatter\NullHighlighter;
use Illuminate\Support\ServiceProvider;
use Laqu\Formatter\QueryFormatter;

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
        $this->app->singleton('queryFormatter', function ($app, $param) {
            return new QueryFormatter($param[0] ?? new NullHighlighter());
        });

        $this->app->singleton('queryHelper', function ($app) {
            return $app->make(QueryHelper::class);
        });
    }
}
