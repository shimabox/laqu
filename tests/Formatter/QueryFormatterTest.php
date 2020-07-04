<?php

declare(strict_types=1);

namespace Laqu\Test;

use Doctrine\SqlFormatter\CliHighlighter;
use Doctrine\SqlFormatter\HtmlHighlighter;
use Laqu\Facades\QueryFormatter;

class QueryFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_format_the_query()
    {
        $query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

        $expected = <<<SQL
SELECT
  count(*),
  `Column1`,
  `Testing`,
  `Testing Three`
FROM
  `Table1`
WHERE
  Column1 = 'testing'
  AND (
    (
      `Column2` = `Column3`
      OR Column4 >= NOW()
    )
  )
GROUP BY
  Column1
ORDER BY
  Column3 DESC
LIMIT
  5, 10
SQL;

        $actual = QueryFormatter::format($query);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_be_formatted_with_a_CliHighlighter()
    {
        $query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

        $expected = <<<SQL
\e[37mSELECT\e[0m
  \e[37mcount\e[0m(*\e[0m),\e[0m
  \e[35;1m`Column1`\e[0m,\e[0m
  \e[35;1m`Testing`\e[0m,\e[0m
  \e[35;1m`Testing Three`\e[0m
\e[37mFROM\e[0m
  \e[35;1m`Table1`\e[0m
\e[37mWHERE\e[0m
  Column1\e[0m =\e[0m \e[34;1m'testing'\e[0m
  \e[37mAND\e[0m (
    (
      \e[35;1m`Column2`\e[0m =\e[0m \e[35;1m`Column3`\e[0m
      \e[37mOR\e[0m Column4\e[0m >\e[0m=\e[0m \e[37mNOW()\e[0m
    )
  )
\e[37mGROUP BY\e[0m
  Column1\e[0m
\e[37mORDER BY\e[0m
  Column3\e[0m \e[37mDESC\e[0m
\e[37mLIMIT\e[0m
  \e[32;1m5\e[0m,\e[0m \e[32;1m10\e[0m

SQL;

        /** @var QueryFormatter */
        $formatter = app()->make('queryFormatter', [new CliHighlighter()]);

        $actual = $formatter->format($query);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_be_formatted_with_a_HtmlHighlighter()
    {
        $query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

        $expected = <<<SQL
<pre style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span>
  <span style="font-weight:bold;">count</span>(<span >*</span>)<span >,</span>
  <span style="color: purple;">`Column1`</span><span >,</span>
  <span style="color: purple;">`Testing`</span><span >,</span>
  <span style="color: purple;">`Testing Three`</span>
<span style="font-weight:bold;">FROM</span>
  <span style="color: purple;">`Table1`</span>
<span style="font-weight:bold;">WHERE</span>
  <span style="color: #333;">Column1</span> <span >=</span> <span style="color: blue;">'testing'</span>
  <span style="font-weight:bold;">AND</span> (
    (
      <span style="color: purple;">`Column2`</span> <span >=</span> <span style="color: purple;">`Column3`</span>
      <span style="font-weight:bold;">OR</span> <span style="color: #333;">Column4</span> <span >&gt;</span><span >=</span> <span style="font-weight:bold;">NOW()</span>
    )
  )
<span style="font-weight:bold;">GROUP BY</span>
  <span style="color: #333;">Column1</span>
<span style="font-weight:bold;">ORDER BY</span>
  <span style="color: #333;">Column3</span> <span style="font-weight:bold;">DESC</span>
<span style="font-weight:bold;">LIMIT</span>
  <span style="color: green;">5</span><span >,</span> <span style="color: green;">10</span></pre>
SQL;

        /** @var QueryFormatter */
        $formatter = app()->make('queryFormatter', [new HtmlHighlighter()]);

        $actual = $formatter->format($query);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_be_highlight_with_a_CliHighlighter()
    {
        $query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

        $expected = <<<SQL
\e[37mSELECT\e[0m \e[37mcount\e[0m(*\e[0m),\e[0m\e[35;1m`Column1`\e[0m,\e[0m\e[35;1m`Testing`\e[0m,\e[0m \e[35;1m`Testing Three`\e[0m \e[37mFROM\e[0m \e[35;1m`Table1`\e[0m
    \e[37mWHERE\e[0m Column1\e[0m =\e[0m \e[34;1m'testing'\e[0m \e[37mAND\e[0m ( (\e[35;1m`Column2`\e[0m =\e[0m \e[35;1m`Column3`\e[0m \e[37mOR\e[0m Column4\e[0m >\e[0m=\e[0m \e[37mNOW()\e[0m) )
    \e[37mGROUP BY\e[0m Column1\e[0m \e[37mORDER BY\e[0m Column3\e[0m \e[37mDESC\e[0m \e[37mLIMIT\e[0m \e[32;1m5\e[0m,\e[0m\e[32;1m10\e[0m

SQL;

        /** @var QueryFormatter */
        $formatter = app()->make('queryFormatter', [new CliHighlighter()]);

        $actual = $formatter->highlight($query);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_be_highlight_with_a_HtmlHighlighter()
    {
        $query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

        $expected = <<<SQL
<pre style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">count</span>(<span >*</span>)<span >,</span><span style="color: purple;">`Column1`</span><span >,</span><span style="color: purple;">`Testing`</span><span >,</span> <span style="color: purple;">`Testing Three`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`Table1`</span>
    <span style="font-weight:bold;">WHERE</span> <span style="color: #333;">Column1</span> <span >=</span> <span style="color: blue;">'testing'</span> <span style="font-weight:bold;">AND</span> ( (<span style="color: purple;">`Column2`</span> <span >=</span> <span style="color: purple;">`Column3`</span> <span style="font-weight:bold;">OR</span> <span style="color: #333;">Column4</span> <span >&gt;</span><span >=</span> <span style="font-weight:bold;">NOW()</span>) )
    <span style="font-weight:bold;">GROUP BY</span> <span style="color: #333;">Column1</span> <span style="font-weight:bold;">ORDER BY</span> <span style="color: #333;">Column3</span> <span style="font-weight:bold;">DESC</span> <span style="font-weight:bold;">LIMIT</span> <span style="color: green;">5</span><span >,</span><span style="color: green;">10</span></pre>
SQL;

        /** @var QueryFormatter */
        $formatter = app()->make('queryFormatter', [new HtmlHighlighter()]);

        $actual = $formatter->highlight($query);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_compress_the_query()
    {
        $query = <<<SQL
-- This is a comment
SELECT
    /* This is another comment
    On more than one line */
    Id #This is one final comment
    as temp, DateCreated as Created FROM MyTable;
SQL;

        $expected = <<<SQL
SELECT Id as temp, DateCreated as Created FROM MyTable;
SQL;

        $actual = QueryFormatter::compress($query);

        $this->assertEquals($expected, $actual);
    }
}
