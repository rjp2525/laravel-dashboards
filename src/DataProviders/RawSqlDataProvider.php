<?php

namespace Reno\Dashboard\DataProviders;

use Closure;
use Illuminate\Support\Facades\DB;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

class RawSqlDataProvider implements DataProvider
{
    /** @var array<int, mixed> */
    protected array $bindings = [];

    protected ?Closure $bindingsCallback = null;

    public function __construct(
        protected string $sql,
        protected ?string $connection = null,
    ) {}

    public static function from(string $sql, ?string $connection = null): self
    {
        return new self($sql, $connection);
    }

    /** @param array<int, mixed> $bindings */
    public function bindings(array $bindings): self
    {
        $this->bindings = $bindings;

        return $this;
    }

    public function bindingsFrom(Closure $callback): self
    {
        $this->bindingsCallback = $callback;

        return $this;
    }

    public function fetch(WidgetContext $context): WidgetData
    {
        $rawBindings = $this->bindingsCallback instanceof Closure
            ? ($this->bindingsCallback)($context)
            : $this->bindings;

        /** @var array<int, mixed> $bindings */
        $bindings = is_array($rawBindings) ? $rawBindings : [];

        $results = DB::connection($this->connection)->select($this->sql, $bindings);

        /** @var array<int, array<string, mixed>> $rows */
        $rows = array_map(fn (mixed $row): array => (array) $row, $results);

        return WidgetData::table(rows: $rows);
    }
}
