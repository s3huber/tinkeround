<?php

namespace Tinkeround\Traits;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

/**
 * Trait including database related methods.
 */
trait DatabaseMethods
{
    /** @var int Query count since last reset */
    protected $queryCount = 0;

    /** @var int Total count of queries made during tinkeround session */
    protected $queryCountTotal = 0;

    /** @var float Query time since last reset in milliseconds */
    protected $queryTime = 0.0;

    /** @var float Overall query time during tinkeround session in milliseconds */
    protected $queryTimeTotal = 0.0;

    /**
     * @return int Total count of queries made during tinkeround session
     */
    public function totalQueryCount(): int
    {
        return $this->queryCountTotal;
    }

    /**
     * @return float Overall query time during tinkeround session in milliseconds
     */
    public function totalQueryTime(): float
    {
        return $this->queryTimeTotal;
    }

    protected function handleQueryExecutedEvent(QueryExecuted $query): void
    {
        $this->queryCount++;
        $this->queryCountTotal++;

        $this->queryTime += $query->time;
        $this->queryTimeTotal += $query->time;
    }

    protected function registerQueryListener(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        DB::listen(function (QueryExecuted $query) {
            $this->handleQueryExecutedEvent($query);
        });
    }
}