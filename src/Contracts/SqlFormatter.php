<?php

declare(strict_types=1);

namespace Laqu\Contracts;

/**
 * @see https://github.com/doctrine/sql-formatter
 */
interface SqlFormatter
{
    /**
     * Format the whitespace in a SQL string to make it easier to read.
     *
     * @param  string $string       The SQL string
     * @param  string $indentString
     * @return string The SQL string with HTML styles and formatting wrapped in a <pre> tag
     */
    public function format(string $string, string $indentString = '  '): string;

    /**
     * Add syntax highlighting to a SQL string
     *
     * @param  string $string The SQL string
     * @return string The SQL string with HTML styles applied
     */
    public function highlight(string $string): string;

    /**
     * Compress a query by collapsing white space and removing comments
     *
     * @param  string $string The SQL string
     * @return string The SQL string without comments
     */
    public function compress(string $string): string;
}
