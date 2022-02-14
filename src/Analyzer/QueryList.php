<?php

declare(strict_types=1);

namespace Laqu\Analyzer;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

class QueryList implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var Query[]
     */
    private $queries;

    /**
     * @param Query[] $queries
     */
    public function __construct(array $queries)
    {
        $this->queries = $queries;
    }

    /**
     * @param QueryList<int, Query> $queryList
     *
     * @return QueryList<int, Query>
     */
    public function addQueryList(QueryList $queryList): self
    {
        foreach ($queryList as $list) {
            $this->queries[] = $list;
        }
        return $this;
    }

    public function extractFastestQuery(): Query
    {
        $sortedQueries = $this->sortByFast();
        return $sortedQueries[0];
    }

    public function extractSlowestQuery(): Query
    {
        $sortedQueries = $this->sortBySlow();
        return $sortedQueries[0];
    }

    /**
     * @return array<int, Query>
     */
    public function sortByFast(): array
    {
        $queries        = $this->queries;
        $executionTimes = $this->executionTimes();
        array_multisort($executionTimes, SORT_ASC, $queries);
        return $queries;
    }

    /**
     * @return array<int, Query>
     */
    public function sortBySlow(): array
    {
        $queries        = $this->queries;
        $executionTimes = $this->executionTimes();
        array_multisort($executionTimes, SORT_DESC, $queries);
        return $queries;
    }

    /**
     * @return array<int, float>
     */
    private function executionTimes(): array
    {
        $times = [];
        array_map(function (Query $query, int $key) use (&$times) {
            $times = array_merge($times, [$key => $query->getTime()]);
        }, $this->queries, array_keys($this->queries));
        return $times;
    }

    /**
     * @param  ?int  $offset
     * @param  Query $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (! $value instanceof Query) {
            throw new InvalidArgumentException('Argument must pass a Query.');
        }

        if ($offset === null) {
            $this->queries[] = $value;
            return;
        }

        $this->queries[$offset] = $value;
    }

    /**
     * @param  int  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->queries[$offset]);
    }

    /**
     * @param  int  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->queries[$offset]);
    }

    /**
     * @param  int    $offset
     * @return ?Query
     */
    public function offsetGet($offset): ?Query
    {
        return $this->queries[$offset] ?? null;
    }

    /**
     * @return ArrayIterator<int, Query>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->queries);
    }

    public function count(): int
    {
        return count($this->queries);
    }
}
