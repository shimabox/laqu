# Laqu

[ Japanese | [English](README-en.md) ]

Laqu is **La**ravel Db **Qu**ery Helper.

![Run Tests](https://github.com/shimabox/laqu/workflows/Run%20Tests/badge.svg?branch=master)
[![License](https://poser.pugx.org/shimabox/laqu/license)](//packagist.org/packages/shimabox/laqu)
[![Latest Stable Version](https://poser.pugx.org/shimabox/laqu/v)](//packagist.org/packages/shimabox/laqu)
[![Maintainability](https://api.codeclimate.com/v1/badges/85b239b6865150a8acc3/maintainability)](https://codeclimate.com/github/shimabox/laqu/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/85b239b6865150a8acc3/test_coverage)](https://codeclimate.com/github/shimabox/laqu/test_coverage)

## Features

- 実行されたdbクエリを確認できます
  - PHPUnit でのアサーション、実行時間による並べ替え、ビルド後のクエリのチェックなど

#### Attention

このライブラリは、開発中に利用されることを想定しています。

## See Also

[【Laravel】実行されたDBクエリの確認ができるやつを書いた - Qiita](https://qiita.com/shimabox/items/11b5d3e3c02e7413fc9e "【Laravel】実行されたDBクエリの確認ができるやつを書いた - Qiita")

## Requirement

- PHP 7.4+ or newer(7.4, 8.0)
- Laravel `6.x`, `7.x`, `8.x`

## Installation

Via composer.
```
$ composer require --dev shimabox/laqu
```

Develop.
```
$ git clone https://github.com/shimabox/laqu.git
$ cd laqu
$ composer install
```

## Usage

### QueryAssertion

期待するクエリが流れているかPHPUnitでアサーションするためのものです。  
traitです。assertQuery()を使います。

```php
<?php

use App\Repositories\ExampleRepository; // example.
use Laqu\QueryAssertion;
use Tests\TestCase;

class QueryAssertionTest extends TestCase
{
    use QueryAssertion;

    private $exampleRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exampleRepository = new ExampleRepository();
    }

    public function queryTest()
    {
        // 基本的な使い方
        $this->assertQuery(
            // クエリが実行される処理をクロージャに渡します
            fn () => $this->exampleRepository->findById('a123'),
            // 期待するクエリを書きます
            'select from user where id = ? and is_active = ?',
            // バインドされる値を配列で定義します
            // (bindするものがない場合は空配列を渡すか、引数は渡さないでOK)
            [
                'a123',
                1,
            ]
        );

        // 複数のクエリを確認
        // 基本的には 1メソッド1クエリの確認 を推奨しますが、中には1メソッドで複数クエリが流れる場合もあると思います。
        // その場合は下記のようにクエリとバインド値を配列で対になるように定義してください。
        $this->assertQuery(
            // 例えばこの処理で複数のクエリが流れるとします
            fn () => $this->exampleRepository->findAll(),
            // 期待するクエリをそれぞれ配列で定義します
            [
                'select from user where is_active = ?', // ※1
                'select from admin_user where id = ? and is_active = ?', // ※2
                'select from something', // ※3
            ],
            // バインドされる値を二次元配列で定義(bindするものがない場合は空配列を渡してください)
            [
                [ // ※1.
                    1,
                ],
                [ // ※2.
                    'b123',
                    1,
                ],
                // ※3 はバインド無しの想定なので空配列を渡します
                [],
            ]
        );
    }
}
```

### QueryAnalyzer

クエリが流れるメソッドを渡して、どのようなクエリが流れたのか確認することができます。  
`QueryAnalyzer::analyze()`で実行されたクエリの結果(`Laqu\Analyzer\QueryList`)が取得できます。  
※ QueryListは`Laqu\Analyzer\Query`を持ちます

```php
<?php

use Laqu\Facades\QueryAnalyzer;

/** @var Laqu\Analyzer\QueryList **/
$analyzed = QueryAnalyzer::analyze(function () {
    $author = Author::find(1);
    $author->delete();
});

/*
Laqu\Analyzer\QueryList {#345
  -queries: array:2 [
    0 => Laqu\Analyzer\Query {#344
      -query: "select * from "authors" where "authors"."id" = ? limit 1"
      -bindings: array:1 [
        0 => 1
      ]
      -time: 0.08
      -buildedQuery: "select * from "authors" where "authors"."id" = 1 limit 1"
    }
    1 => Laqu\Analyzer\Query {#337
      -query: "delete from "authors" where "id" = ?"
      -bindings: array:1 [
        0 => "1"
      ]
      -time: 0.03
      -buildedQuery: "delete from "authors" where "id" = '1'"
    }
  ]
}
*/
dump($analyzed);

// select * from "authors" where "authors"."id" = 1 limit 1
echo $analyzed[0]->getBuildedQuery();
// delete from "authors" where "id" = '1'
echo $analyzed[1]->getBuildedQuery();

/*
Laqu\Analyzer\Query {#337
  -query: "delete from "authors" where "id" = ?"
  -bindings: array:1 [
    0 => "1"
  ]
  -time: 0.03
  -buildedQuery: "delete from "authors" where "id" = '1'"
}
*/
dump($analyzed->extractFastestQuery());

/*
Laqu\Analyzer\Query {#344
  -query: "select * from "authors" where "authors"."id" = ? limit 1"
  -bindings: array:1 [
    0 => 1
  ]
  -time: 0.08
  -buildedQuery: "select * from "authors" where "authors"."id" = 1 limit 1"
}
*/
dump($analyzed->extractSlowestQuery());

/*
array:2 [
  0 => Laqu\Analyzer\Query {#337
    -query: "delete from "authors" where "id" = ?"
    -bindings: array:1 [
      0 => "1"
    ]
    -time: 0.03
    -buildedQuery: "delete from "authors" where "id" = '1'"
  }
  1 => Laqu\Analyzer\Query {#344
    -query: "select * from "authors" where "authors"."id" = ? limit 1"
    -bindings: array:1 [
      0 => 1
    ]
    -time: 0.08
    -buildedQuery: "select * from "authors" where "authors"."id" = 1 limit 1"
  }
]
*/
dump($analyzed->sortByFast());

/*
array:2 [
  0 => Laqu\Analyzer\Query {#344
    -query: "select * from "authors" where "authors"."id" = ? limit 1"
    -bindings: array:1 [
      0 => 1
    ]
    -time: 0.08
    -buildedQuery: "select * from "authors" where "authors"."id" = 1 limit 1"
  }
  1 => Laqu\Analyzer\Query {#337
    -query: "delete from "authors" where "id" = ?"
    -bindings: array:1 [
      0 => "1"
    ]
    -time: 0.02
    -buildedQuery: "delete from "authors" where "id" = '1'"
  }
]
*/
dump($analyzed->sortBySlow());
```

### Helper

#### QueryLog

QueryLogは [Basic Database Usage - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/5.0/database#query-logging "Basic Database Usage - Laravel - The PHP Framework For Web Artisans") の処理をラップしたものです。  
※ 実行時間関連はそこまで精密ではありません

```php
<?php

use Laqu\Facades\QueryLog;

$queryLog = QueryLog::getQueryLog(fn () => Author::find(1));

/*
array:1 [
  0 => array:3 [
    "query" => "select * from "authors" where "authors"."id" = ? limit 1"
    "bindings" => array:1 [
      0 => 1
    ]
    "time" => 0.12
  ]
]
*/
dump($queryLog);
```

#### QueryHelper

クエリとバインドパラメータを渡すと、実行されるクエリの確認ができます。  
[pdo-debug/pdo-debug.php at master · panique/pdo-debug](https://github.com/panique/pdo-debug/blob/master/pdo-debug.php "pdo-debug/pdo-debug.php at master · panique/pdo-debug") を参考にしています。

```php
<?php

use Laqu\Facades\QueryHelper;

$now  = Carbon::now();
$from = $now->copy()->subDay();
$to   = $now->copy()->addDay();

$query = 'select * from authors where id in (?, ?) and name like :name and updated_at between ? and ?';

$bindings = [
    1,
    2,
    '%Shakespeare',
    $from,
    $to,
];

$buildedQuery = QueryHelper::buildedQuery($query, $bindings);

// select * from authors where id in (1, 2) and name like '%Shakespeare' and updated_at between '2020-07-07 00:37:55' and '2020-07-09 00:37:55'
echo $buildedQuery;
```

### QueryFormatter

QueryFormatterは`Doctrine\SqlFormatter\SqlFormatter`のラッパーです。  
@see [doctrine/sql-formatter: A lightweight php class for formatting sql statements. Handles automatic indentation and syntax highlighting.](https://github.com/doctrine/sql-formatter "doctrine/sql-formatter: A lightweight php class for formatting sql statements. Handles automatic indentation and syntax highlighting.")

デフォルトは`NullHighlighter`を利用していますが、CLI、HTMLでのフォーマットも可能です。

#### format()

デフォルトのHighlighterは`Doctrine\SqlFormatter\NullHighlighter`です。

```php
<?php

use Laqu\Facades\QueryFormatter;

$query = "SELECT count(*),`Column1`,`Testing`, `Testing Three` FROM `Table1`
    WHERE Column1 = 'testing' AND ( (`Column2` = `Column3` OR Column4 >= NOW()) )
    GROUP BY Column1 ORDER BY Column3 DESC LIMIT 5,10";

/*
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
*/
echo QueryFormatter::format($query);
```

`Doctrine\SqlFormatter\CliHighlighter`を利用する場合は以下の通り、`CliHighlighter`をインジェクトしてください。

```php
<?php

use Doctrine\SqlFormatter\CliHighlighter;
use Laqu\Formatter\QueryFormatter;

/** @var QueryFormatter */
$formatter = app()->make(QueryFormatter::class/* or 'queryFormatter' */, [new CliHighlighter()]);

echo $formatter->format($query);
```
出力結果  
![example_CliHighlighter](https://github.com/shimabox/assets/raw/master/laqu/example_CliHighlighter.png)

`Doctrine\SqlFormatter\HtmlHighlighter`を利用する場合は以下の通り、`HtmlHighlighter`をインジェクトしてください。

```php
<?php

use Doctrine\SqlFormatter\HtmlHighlighter;
use Laqu\Formatter\QueryFormatter;

/** @var QueryFormatter */
$formatter = app()->make(QueryFormatter::class/* or 'queryFormatter' */, [new HtmlHighlighter()]);

echo $formatter->format($query);
```
出力結果  
![example_HtmlHighlighter](https://github.com/shimabox/assets/raw/master/laqu/example_HtmlHighlighter.png)

#### highlight()

使い方は `format()` とほぼ同じです。  
https://github.com/doctrine/sql-formatter#syntax-highlighting-only を参照してください。

```php
<?php

use Laqu\Facades\QueryFormatter;

$query = '...';

echo QueryFormatter::highlight($query);
```

#### compress()

`compress()`はすべてのコメントと余計な空白を取り除いたクエリを返却します。  
https://github.com/doctrine/sql-formatter#compress-query を参照してください。

```php
<?php

use Laqu\Facades\QueryFormatter;

$query = <<<SQL
-- This is a comment
SELECT
    /* This is another comment
    On more than one line */
    Id #This is one final comment
    as temp, DateCreated as Created FROM MyTable;
SQL;

// SELECT Id as temp, DateCreated as Created FROM MyTable;
echo QueryFormatter::compress($query);
```

## Develop

### Run php-cs-fixer.

check.
```
$ composer phpcs
```

fix.
```
$ composer phpcs:fix
```

### Run phpstan(larastan).

check.
```
$ composer phpstan
```

### Run test.

```
$ composer test
```

### Run ci.

Run all the above commands at once.

```
$ composer ci
```

## TODO

テストで確認しているクエリのパターンがまだまだ少ないです。