<?php

declare(strict_types=1);

namespace Laqu\Analyzer;

use Laqu\Facades\QueryHelper;
use Laqu\Facades\QueryLog;

class QueryAnalyzer
{
    /**
     * @param  callable       $queryCaller Process to execute the query.
     * @return QueryList<int, Query>
     */
    public function analyze(callable $queryCaller): QueryList
    {
        $queryLog  = QueryLog::getQueryLog($queryCaller);
        $queryList = array_map(function ($query) {
            return new Query(
                $query['query'],
                $query['bindings'],
                $query['time'],
                QueryHelper::buildedQuery($query['query'], $query['bindings'])
            );
        }, $queryLog);

        return new QueryList($queryList);
    }
}
