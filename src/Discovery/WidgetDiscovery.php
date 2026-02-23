<?php

namespace Reno\Dashboard\Discovery;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use Reno\Dashboard\Attributes\AsWidget;
use Reno\Dashboard\Attributes\DashboardStat;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\DataProviders\EloquentDataProvider;
use Reno\Dashboard\InlineWidget;
use Reno\Dashboard\Support\GridPosition;
use SplFileInfo;

class WidgetDiscovery
{
    protected AttributeScanner $scanner;

    public function __construct(
        protected DashboardManager $manager,
    ) {
        $this->scanner = new AttributeScanner;
    }

    /**
     * Scan paths and register all discovered widgets.
     *
     * @param  array<int, string>  $paths
     */
    public function discover(array $paths): void
    {
        $discovered = $this->scanPaths($paths);

        foreach ($discovered as $widget) {
            $this->registerDiscovered($widget);
        }
    }

    /**
     * Scan directories for classes with dashboard attributes.
     *
     * @param  array<int, string>  $paths
     * @return DiscoveredWidget[]
     */
    public function scanPaths(array $paths): array
    {
        $widgets = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                $className = $this->classNameFromFile($file);

                if ($className === null || ! class_exists($className)) {
                    continue;
                }

                if (! $this->hasDiscoverableAttributes($className)) {
                    continue;
                }

                $widgets = array_merge($widgets, $this->scanner->scan($className));
            }
        }

        return $widgets;
    }

    public function registerDiscovered(DiscoveredWidget $discovered): void
    {
        $provider = match ($discovered->source) {
            'stat' => $this->buildEloquentProvider($discovered),
            'method' => CallbackDataProvider::from($discovered->className::{$discovered->methodName}(...)),
            default => CallbackDataProvider::from(fn (): null => null),
        };

        $widget = new InlineWidget(
            key: $discovered->key,
            label: $discovered->label,
            type: $discovered->type,
            icon: $discovered->icon,
            description: $discovered->description,
            componentName: null,
            position: new GridPosition,
            provider: $provider,
            cacheTtlSeconds: $discovered->cacheTtl,
            requiredPermissions: $discovered->permissions,
        );

        $this->manager->registerInline($discovered->key, $widget);
    }

    private function buildEloquentProvider(DiscoveredWidget $discovered): EloquentDataProvider
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $discovered->className;
        $provider = EloquentDataProvider::for($modelClass);

        if ($discovered->dateColumn) {
            $provider->dateColumn($discovered->dateColumn);
        }

        $aggregate = $discovered->aggregate ?? 'count';
        $column = $discovered->aggregateColumn;

        match ($aggregate) {
            'count' => $provider->count(),
            'sum' => $provider->sum($column ?? '*'),
            'avg' => $provider->avg($column ?? '*'),
            'min' => $provider->min($column ?? '*'),
            'max' => $provider->max($column ?? '*'),
            default => $provider->count(),
        };

        if ($discovered->scope) {
            $provider->scope($discovered->scope);
        }

        return $provider;
    }

    private function classNameFromFile(SplFileInfo $file): ?string
    {
        $contents = file_get_contents($file->getPathname());

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        if ($namespace && $class) {
            return "{$namespace}\\{$class}";
        }

        return null;
    }

    /**
     * @param  class-string  $className
     */
    private function hasDiscoverableAttributes(string $className): bool
    {
        $reflection = new ReflectionClass($className);

        if ($reflection->getAttributes(DashboardStat::class) !== []) {
            return true;
        }

        foreach ($reflection->getMethods() as $method) {
            if ($method->getAttributes(AsWidget::class) !== []) {
                return true;
            }
        }

        return false;
    }
}
