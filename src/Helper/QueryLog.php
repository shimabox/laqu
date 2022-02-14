<?php

declare(strict_types=1);

namespace Laqu\Helper;

use Illuminate\Database\Connection;

class QueryLog
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection query log.
     *
     * @param callable $queryCaller
     *
     * @return array<int, array{"query": string, "bindings": array<int, mixed>, "time": float}>
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
