<?php

declare(strict_types=1);

namespace Laqu\Analyzer;

class Query
{
    /** @var string */
    private $query;

    /** @var array<int|string, int|string>|array{} */
    private $bindings;

    /** @var float */
    private $time;

    /** @var string */
    private $buildedQuery;

    /**
     * @param string          $query
     * @param array<int|string, int|string>|array{} $bindings
     * @param float           $time
     * @param string          $buildedQuery
     */
    public function __construct(
        string $query,
        array $bindings,
        float $time,
        string $buildedQuery
    ) {
        $this->query        = $query;
        $this->bindings     = $bindings;
        $this->time         = $time;
        $this->buildedQuery = $buildedQuery;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return array<int|string, int|string>|array{}
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getBuildedQuery(): string
    {
        return $this->buildedQuery;
    }
}
