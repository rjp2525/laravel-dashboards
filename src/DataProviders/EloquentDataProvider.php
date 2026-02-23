<?php

namespace Reno\Dashboard\DataProviders;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

class EloquentDataProvider implements DataProvider
{
    protected ?string $dateColumn = 'created_at';

    protected ?string $aggregate = null;

    protected ?string $aggregateColumn = null;

    /** @var array<int, string> */
    protected array $scopes = [];

    protected ?Closure $queryCallback = null;

    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(
        protected string $model,
    ) {}

    /**
     * @param  class-string<Model>  $model
     */
    public static function for(string $model): self
    {
        return new self($model);
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

    public function min(string $column): self
    {
        $this->aggregate = 'min';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function max(string $column): self
    {
        $this->aggregate = 'max';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function scope(string ...$scopes): self
    {
        $this->scopes = array_merge($this->scopes, array_values($scopes));

        return $this;
    }

    /** @param Closure(Builder<Model>, WidgetContext):void $callback */
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
     * @return Builder<Model>
     */
    protected function buildScopedQuery(WidgetContext $context, array $dateRange): Builder
    {
        $query = $this->buildQuery($context);

        if ($this->dateColumn) {
            $query->whereBetween($this->dateColumn, $dateRange);
        }

        if ($context->tenantId) {
            /** @var string $tenantColumn */
            $tenantColumn = config('dashboard.tenancy.column', 'tenant_id');
            $query->where($tenantColumn, $context->tenantId);
        }

        return $query;
    }

    /** @return Builder<Model> */
    protected function buildQuery(WidgetContext $context): Builder
    {
        /** @var Model $model */
        $model = new $this->model;
        $query = $model->newQuery();

        foreach ($this->scopes as $scope) {
            $query->{$scope}();
        }

        if ($this->queryCallback instanceof Closure) {
            ($this->queryCallback)($query, $context);
        }

        return $query;
    }

    /**
     * @param Builder<Model> $query */
    protected function executeAggregate(Builder $query): mixed
    {
        return match ($this->aggregate) {
            'count' => $query->count(),
            'sum' => $query->sum($this->aggregateColumn ?? '*'),
            'avg' => $query->avg($this->aggregateColumn ?? '*'),
            'min' => $query->min($this->aggregateColumn ?? '*'),
            'max' => $query->max($this->aggregateColumn ?? '*'),
            default => $query->count(),
        };
    }
}
