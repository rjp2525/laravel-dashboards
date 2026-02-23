<?php

namespace Reno\Dashboard\DataProviders;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

class QueryBuilderDataProvider implements DataProvider
{
    protected ?string $dateColumn = 'created_at';

    protected ?string $aggregate = null;

    protected ?string $aggregateColumn = null;

    protected ?Closure $queryCallback = null;

    public function __construct(
        protected string $table,
        protected ?string $connection = null,
    ) {}

    public static function for(string $table, ?string $connection = null): self
    {
        return new self($table, $connection);
    }

    public function dateColumn(string $column): self
    {
        $this->dateColumn = $column;

        return $this;
    }

    public function count(): self
    {
        $this->aggregate = 'count';
        $this->aggregateColumn = '*';

        return $this;
    }

    public function sum(string $column): self
    {
        $this->aggregate = 'sum';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function avg(string $column): self
    {
        $this->aggregate = 'avg';
        $this->aggregateColumn = $column;

        return $this;
    }

    /** @param Closure(Builder, WidgetContext): void $callback */
    public function query(Closure $callback): self
    {
        $this->queryCallback = $callback;

        return $this;
    }

    public function fetch(WidgetContext $context): WidgetData
    {
        $currentValue = $this->executeAggregate(
            $this->buildScopedQuery($context, $context->dateRange()),
        );

        $previousValue = $this->executeAggregate(
            $this->buildScopedQuery($context, $context->previousDateRange()),
        );

        return WidgetData::stat($currentValue, $previousValue);
    }

    /**
     * @param  array{0: \DateTimeInterface, 1: \DateTimeInterface}  $dateRange
     */
    protected function buildScopedQuery(WidgetContext $context, array $dateRange): Builder
    {
        $query = $this->buildQuery($context);

        if ($this->dateColumn) {
            $query->whereBetween($this->dateColumn, $dateRange);
        }

        return $query;
    }

    protected function buildQuery(WidgetContext $context): Builder
    {
        $query = DB::connection($this->connection)->table($this->table);

        if ($this->queryCallback instanceof Closure) {
            ($this->queryCallback)($query, $context);
        }

        return $query;
    }

    protected function executeAggregate(Builder $query): mixed
    {
        return match ($this->aggregate) {
            'count' => $query->count(),
            'sum' => $query->sum($this->aggregateColumn ?? '*'),
            'avg' => $query->avg($this->aggregateColumn ?? '*'),
            default => $query->count(),
        };
    }
}
