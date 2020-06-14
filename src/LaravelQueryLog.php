<?php

declare(strict_types=1);

namespace LaravelQueryAssertion;

use Illuminate\Database\Connection;

class LaravelQueryLog
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection query log.
     *
     * @param  callable $queryCaller
     * @return array
     */
    public function getQueryLog(callable $queryCaller): array
    {
        $this->connection->enableQueryLog();

        $queryCaller();

        $queryResult = $this->connection->getQueryLog();

        $this->connection->disableQueryLog();

        return $queryResult;
    }
}
