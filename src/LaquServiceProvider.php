<?php

declare(strict_types=1);

namespace Laqu;

use Doctrine\SqlFormatter\NullHighlighter;
use Illuminate\Support\ServiceProvider;
use Laqu\Analyzer\QueryAnalyzer;
use Laqu\Formatter\QueryFormatter;
use Laqu\Helper\QueryHelper;
use Laqu\Helper\QueryLog;

class LaquServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('queryLog', function ($app) {
            return $app->make(QueryLog::class);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('queryFormatter', function ($app, $param) {
            return new QueryFormatter($param[0] ?? new NullHighlighter());
        });

        $this->app->singleton('queryHelper', function ($app) {
            return $app->make(QueryHelper::class);
        });

        $this->app->singleton('queryAnalyzer', function ($app) {
            return $app->make(QueryAnalyzer::class);
        });
    }
}
