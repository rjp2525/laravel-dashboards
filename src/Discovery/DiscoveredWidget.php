<?php

namespace Reno\Dashboard\Discovery;

use Reno\Dashboard\Enums\WidgetType;

class DiscoveredWidget
{
    /**
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly WidgetType $type,
        public readonly string $source,
        public readonly string $className,
        public readonly ?string $methodName = null,
        public readonly ?string $aggregate = null,
        public readonly ?string $aggregateColumn = null,
        public readonly ?string $dateColumn = 'created_at',
        public readonly ?string $scope = null,
        public readonly ?string $dashboard = null,
        public readonly ?string $icon = null,
        public readonly ?string $description = null,
        public readonly int $cacheTtl = 300,
        public readonly array $permissions = [],
    ) {}

    /**
     * @param  class-string  $className
     * @param  array<int, string>  $permissions
     */
    public static function fromStat(
        string $key,
        string $label,
        string $className,
        string $aggregate = 'count',
        ?string $aggregateColumn = null,
        string $dateColumn = 'created_at',
        ?string $scope = null,
        ?string $dashboard = null,
        ?string $icon = null,
        int $cacheTtl = 300,
        array $permissions = [],
    ): self {
        return new self(
            key: $key,
            label: $label,
            type: WidgetType::STAT,
            source: 'stat',
            className: $className,
            aggregate: $aggregate,
            aggregateColumn: $aggregateColumn,
            dateColumn: $dateColumn,
            scope: $scope,
            dashboard: $dashboard,
            icon: $icon,
            cacheTtl: $cacheTtl,
            permissions: $permissions,
        );
    }

    /**
     * @param  class-string  $className
     * @param  array<int, string>  $permissions
     */
    public static function fromMethod(
        string $key,
        string $label,
        WidgetType $type,
        string $className,
        string $methodName,
        ?string $dashboard = null,
        ?string $icon = null,
        ?string $description = null,
        int $cacheTtl = 300,
        array $permissions = [],
    ): self {
        return new self(
            key: $key,
            label: $label,
            type: $type,
            source: 'method',
            className: $className,
            methodName: $methodName,
            dashboard: $dashboard,
            icon: $icon,
            description: $description,
            cacheTtl: $cacheTtl,
            permissions: $permissions,
        );
    }

    /**
     * @param  class-string  $className
     */
    public static function generateStatKey(string $className, string $aggregate, ?string $column): string
    {
        $base = class_basename($className);
        $snake = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($base)) ?? $base);

        if ($aggregate === 'count') {
            return "{$snake}_{$aggregate}";
        }

        $columnSnake = $column ? strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($column)) ?? $column) : '';

        return "{$snake}_{$aggregate}_{$columnSnake}";
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type->value,
            'source' => $this->source,
            'class_name' => $this->className,
            'method_name' => $this->methodName,
            'aggregate' => $this->aggregate,
            'aggregate_column' => $this->aggregateColumn,
            'date_column' => $this->dateColumn,
            'scope' => $this->scope,
            'dashboard' => $this->dashboard,
            'icon' => $this->icon,
            'description' => $this->description,
            'cache_ttl' => $this->cacheTtl,
            'permissions' => $this->permissions,
        ];
    }

    /**
     * @param  array{key: string, label: string, type: string, source: string, class_name: string, method_name?: string|null, aggregate?: string|null, aggregate_column?: string|null, date_column?: string|null, scope?: string|null, dashboard?: string|null, icon?: string|null, description?: string|null, cache_ttl?: int, permissions?: array<int, string>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            label: $data['label'],
            type: WidgetType::from($data['type']),
            source: $data['source'],
            className: $data['class_name'],
            methodName: $data['method_name'] ?? null,
            aggregate: $data['aggregate'] ?? null,
            aggregateColumn: $data['aggregate_column'] ?? null,
            dateColumn: $data['date_column'] ?? 'created_at',
            scope: $data['scope'] ?? null,
            dashboard: $data['dashboard'] ?? null,
            icon: $data['icon'] ?? null,
            description: $data['description'] ?? null,
            cacheTtl: $data['cache_ttl'] ?? 300,
            permissions: $data['permissions'] ?? [],
        );
    }
}
