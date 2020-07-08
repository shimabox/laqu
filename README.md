# Laqu

Laqu is **La**ravel Db **Qu**ery Helper.

![Run Tests](https://github.com/shimabox/laqu/workflows/Run%20Tests/badge.svg?branch=master)
[![License](https://poser.pugx.org/shimabox/laqu/license)](//packagist.org/packages/shimabox/laqu)
[![Latest Stable Version](https://poser.pugx.org/shimabox/laqu/v)](//packagist.org/packages/shimabox/laqu)
[![Maintainability](https://api.codeclimate.com/v1/badges/85b239b6865150a8acc3/maintainability)](https://codeclimate.com/github/shimabox/laqu/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/85b239b6865150a8acc3/test_coverage)](https://codeclimate.com/github/shimabox/laqu/test_coverage)

## Features

- Can check the executed db query
  - Assertions in PHPUnit, sort by execution time, check queries after build, etc

#### Attention
This library is intended to be used during development.

## Requirement

- PHP 7.2+ or newer
- Laravel `5.8.x`, `6.x`, `7.x`

## Installation

Via composer.
```
$ composer require --dev shimabox/laqu
```

Develop.
```
$ git clone https://github.com/shimabox/laqu.git
```

## Usage

### QueryAssertion

Available within PHPUnit.

```php
<?php

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
        // Basic usage.
        $this->assertQuery(
            // Pass the process in which the query will be executed in the closure.
            function () {
                $this->exampleRepository->findById('a123');
            },
            // Write the expected query.
            'select from user where id = ? and is_active = ?',
            // Define the expected bind values as an array.
            // (If there is nothing to bind, pass an empty array or do not pass the argument)
            [
                'a123',
                1,
            ]
        );

        // Assert multiple queries.
        // Basically, it's a good idea to look at one query in one method,
        // but there are cases where one method executes multiple queries.
        // In that case, define the query and bind value as an array pair as shown below.
        $this->assertQuery(
            function () {
                // For example, if multiple queries are executed in this process
                $this->exampleRepository->findAll();
            },
            // Define an array for each expected query.
            [
                'select from user where is_active = ?', // ※1
                'select from admin_user where id = ? and is_active = ?', // ※2
                'select from something', // ※3
            ],
            // Define the bind values ​​as a two-dimensional array (pass empty array if there is nothing to bind).
            [
                [ // ※1.
                    1,
                ],
                [ // ※2.
                    'b123',
                    1,
                ],
                // ※3 is no bind.
                [],
            ]
        );
    }
}
```

### QueryAnalyzer

```php
<?php

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

QueryLog is a wrap on [Basic Database Usage - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/5.0/database#query-logging "Basic Database Usage - Laravel - The PHP Framework For Web Artisans") process.

```php
<?php

$queryLog = QueryLog::getQueryLog(function () {
    Author::find(1);
});

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

```php
<?php
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


## Run php-cs-fixer.

check.
```
$ composer phpcs
```

fix.
```
$ composer phpcs:fix
```

## Run test.

```
$ composer test
```

## TODO

There are not yet enough query patterns.