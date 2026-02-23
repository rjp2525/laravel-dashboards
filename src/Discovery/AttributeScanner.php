<?php

namespace Reno\Dashboard\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Reno\Dashboard\Attributes\AsWidget;
use Reno\Dashboard\Attributes\Dashboardable;
use Reno\Dashboard\Attributes\DashboardStat;
use RuntimeException;

class AttributeScanner
{
    /**
     * @param  class-string  $className
     * @return DiscoveredWidget[]
     */
    public function scan(string $className): array
    {
        $reflection = new ReflectionClass($className);
        $widgets = [];

        $dashboardable = $this->getDashboardable($reflection);

        foreach ($this->getStatAttributes($reflection) as $stat) {
            $widgets[] = $this->buildFromStat($stat, $className, $dashboardable);
        }

        foreach ($reflection->getMethods() as $method) {
            $asWidget = $this->getAsWidgetAttribute($method);

            if ($asWidget !== null) {
                $this->validateAsWidgetMethod($method);
                $widgets[] = $this->buildFromMethod($asWidget, $className, $method->getName());
            }
        }

        return $widgets;
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     */
    private function getDashboardable(ReflectionClass $reflection): ?Dashboardable
    {
        $attrs = $reflection->getAttributes(Dashboardable::class);

        if ($attrs === []) {
            return null;
        }

        return $attrs[0]->newInstance();
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @return DashboardStat[]
     */
    private function getStatAttributes(ReflectionClass $reflection): array
    {
        $attrs = $reflection->getAttributes(DashboardStat::class);

        return array_map(
            fn ($attr): DashboardStat => $attr->newInstance(),
            $attrs,
        );
    }

    private function getAsWidgetAttribute(ReflectionMethod $method): ?AsWidget
    {
        $attrs = $method->getAttributes(AsWidget::class);

        if ($attrs === []) {
            return null;
        }

        return $attrs[0]->newInstance();
    }

    private function validateAsWidgetMethod(ReflectionMethod $method): void
    {
        if (! $method->isPublic() || ! $method->isStatic()) {
            throw new RuntimeException(
                "Method [{$method->getDeclaringClass()->getName()}::{$method->getName()}()] "
                .'must be public and static to use #[AsWidget].',
            );
        }
    }

    /**
     * @param  class-string  $className
     */
    private function buildFromStat(DashboardStat $stat, string $className, ?Dashboardable $dashboardable): DiscoveredWidget
    {
        $dateColumn = $stat->dateColumn ?? ($dashboardable !== null ? $dashboardable->dateColumn : 'created_at');
        $dashboard = $stat->dashboard ?? $dashboardable?->dashboard;
        $scope = $stat->scope ?? $dashboardable?->scope;

        $key = $stat->key ?? DiscoveredWidget::generateStatKey($className, $stat->aggregate, $stat->column);

        return DiscoveredWidget::fromStat(
            key: $key,
            label: $stat->label,
            className: $className,
            aggregate: $stat->aggregate,
            aggregateColumn: $stat->column,
            dateColumn: $dateColumn,
            scope: $scope,
            dashboard: $dashboard,
            icon: $stat->icon,
            cacheTtl: $stat->cacheTtl,
            permissions: $stat->permissions,
        );
    }

    /**
     * @param  class-string  $className
     */
    private function buildFromMethod(AsWidget $attr, string $className, string $methodName): DiscoveredWidget
    {
        return DiscoveredWidget::fromMethod(
            key: $attr->key,
            label: $attr->label,
            type: $attr->type,
            className: $className,
            methodName: $methodName,
            dashboard: $attr->dashboard,
            icon: $attr->icon,
            description: $attr->description,
            cacheTtl: $attr->cacheTtl,
            permissions: $attr->permissions,
        );
    }
}
