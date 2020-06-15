<?php

declare(strict_types=1);

namespace Laqu\SqlFormatter;

use Doctrine\SqlFormatter\Highlighter as DoctrineHighlighter;
use Doctrine\SqlFormatter\SqlFormatter as DoctrineSqlFormatter;
use Laqu\Contracts\SqlFormatter as SqlFormatterContract;

/**
 * @see https://github.com/doctrine/sql-formatter
 */
class SqlFormatter implements SqlFormatterContract
{
    private $sqlFormatter;

    public function __construct(?DoctrineHighlighter $highlighter = null)
    {
        $this->sqlFormatter = new DoctrineSqlFormatter($highlighter);
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $string, string $indentString = '  '): string
    {
        return $this->sqlFormatter->format($string, $indentString);
    }

    /**
     * {@inheritdoc}
     */
    public function highlight(string $string): string
    {
        return $this->sqlFormatter->highlight($string);
    }

    /**
     * {@inheritdoc}
     */
    public function compress(string $string): string
    {
        return $this->sqlFormatter->compress($string);
    }
}
