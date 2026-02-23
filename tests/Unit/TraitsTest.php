<?php

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\ChartSeries;
use Reno\Dashboard\Support\RefreshConfig;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\AbstractWidget;
use Reno\Dashboard\Widgets\StatWidget;
use Reno\Dashboard\Widgets\TableWidget;

test('cacheTtl() returns default from config', function (): void {
    config(['dashboard.cache.default_ttl' => 600]);

    $widget = makeStatWidget('cache-ttl');

    expect($widget->cacheTtl())->toBe(600);
});

test('cacheKey() uses config prefix', function (): void {
    config(['dashboard.cache.prefix' => 'my-app']);

    $widget = makeStatWidget('revenue');

    expect($widget->cacheKey())->toBe('my-app:widget:revenue');
});

test('buildCacheKey() appends segments', function (): void {
    config(['dashboard.cache.prefix' => 'dashboard']);

    $widget = makeStatWidget('revenue');

    $key = $widget->buildCacheKey('period', '7d', 'filters', 'abc123');

    expect($key)->toBe('dashboard:widget:revenue:period:7d:filters:abc123');
});

test('getCached() returns callback result when cache disabled', function (): void {
    config(['dashboard.cache.enabled' => false]);

    $widget = makeStatWidget('no-cache');

    $result = $widget->getCached('test-key', fn (): string => 'fresh-data');

    expect($result)->toBe('fresh-data');
});

test('getCached() caches result when enabled', function (): void {
    config(['dashboard.cache.enabled' => true]);
    config(['dashboard.cache.store' => null]);

    $widget = makeStatWidget('cached');
    $callCount = 0;

    $callback = function () use (&$callCount): string {
        $callCount++;

        return 'cached-value';
    };

    $result1 = $widget->getCached('cache-test-key', $callback);
    $result2 = $widget->getCached('cache-test-key', $callback);

    expect($result1)->toBe('cached-value');
    expect($result2)->toBe('cached-value');
    expect($callCount)->toBe(1);
});

test('forgetCache() removes cached value', function (): void {
    config(['dashboard.cache.enabled' => true]);
    config(['dashboard.cache.store' => null]);

    $widget = makeStatWidget('forget');
    $callCount = 0;

    $callback = function () use (&$callCount): string {
        $callCount++;

        return 'value-'.$callCount;
    };

    $widget->getCached('forget-key', $callback);
    expect($callCount)->toBe(1);

    $widget->forgetCache('forget-key');

    $result = $widget->getCached('forget-key', $callback);
    expect($callCount)->toBe(2);
    expect($result)->toBe('value-2');
});

test('resolveWithComparison() calls resolveCurrentValue and resolvePreviousValue', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'comparison';
        }

        public function label(): string
        {
            return 'Comparison';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(100, 80));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 100;
        }

        protected function resolvePreviousValue(WidgetContext $context): mixed
        {
            return 80;
        }
    };

    $context = new WidgetContext;
    $result = $widget->resolveWithComparison($context);

    expect($result)->toBeInstanceOf(WidgetData::class);
    expect($result->value)->toBe(100);
    expect($result->previousValue)->toBe(80);
    expect($result->change)->toBe(20);
});

test('resolveWithComparison() returns WidgetData::stat with both values', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'compare-stat';
        }

        public function label(): string
        {
            return 'Compare Stat';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(50));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 50;
        }
    };

    $context = new WidgetContext;
    $result = $widget->resolveWithComparison($context);

    expect($result)->toBeInstanceOf(WidgetData::class);
    expect($result->value)->toBe(50);
    expect($result->previousValue)->toBeNull();
});

test('resolveDateRange() delegates to context', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'date-range';
        }

        public function label(): string
        {
            return 'Date Range';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(1));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 1;
        }
    };

    $context = new WidgetContext(period: Period::TODAY);
    $range = $widget->resolveDateRange($context);

    expect($range)->toBeArray()->toHaveCount(2);
    expect($range[0]->toDateString())->toBe($range[1]->toDateString());
});

test('resolvePreviousDateRange() delegates to context', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'prev-range';
        }

        public function label(): string
        {
            return 'Prev Range';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(1));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 1;
        }
    };

    $context = new WidgetContext(period: Period::SEVEN_DAYS);
    $range = $widget->resolvePreviousDateRange($context);

    expect($range)->toBeArray()->toHaveCount(2);
});

test('refreshStrategy() returns RefreshConfig', function (): void {
    $widget = makeStatWidget('refresh');

    $config = $widget->refreshStrategy();

    expect($config)->toBeInstanceOf(RefreshConfig::class);
    expect($config->strategy)->toBe(RefreshStrategy::POLL);
    expect($config->interval)->toBe(60);
});

test('refreshInterval() returns seconds', function (): void {
    $widget = makeStatWidget('refresh-interval');

    expect($widget->refreshInterval())->toBe(60);
});

test('refreshUsing() changes strategy', function (): void {
    $widget = makeStatWidget('refresh-using');

    $widget->refreshUsing(RefreshStrategy::PUSH, 0);

    expect($widget->refreshStrategy()->strategy)->toBe(RefreshStrategy::PUSH);
    expect($widget->refreshStrategy()->interval)->toBe(0);
});

test('pollEvery() sets poll strategy with interval', function (): void {
    $widget = makeStatWidget('poll-every');

    $widget->pollEvery(30);

    $config = $widget->refreshStrategy();
    expect($config->strategy)->toBe(RefreshStrategy::POLL);
    expect($config->interval)->toBe(30);
});

test('pushUpdates() sets push strategy', function (): void {
    $widget = makeStatWidget('push-updates');

    $widget->pushUpdates();

    $config = $widget->refreshStrategy();
    expect($config->strategy)->toBe(RefreshStrategy::PUSH);
    expect($config->interval)->toBe(0);
});

test('manualRefresh() sets manual strategy', function (): void {
    $widget = makeStatWidget('manual-refresh');

    $widget->manualRefresh();

    $config = $widget->refreshStrategy();
    expect($config->strategy)->toBe(RefreshStrategy::MANUAL);
    expect($config->interval)->toBe(0);
});

test('authorize() returns true when no permissions required', function (): void {
    $widget = makeStatWidget('no-perms');

    expect($widget->authorize(null))->toBeTrue();
});

test('authorize() returns false when user is null and permissions set', function (): void {
    $widget = makeStatWidget('with-perms');
    $widget->permissions(['view-dashboard']);

    expect($widget->authorize(null))->toBeFalse();
});

test('permissions() sets required permissions', function (): void {
    $widget = makeStatWidget('set-perms');
    $widget->permissions(['admin', 'view-widgets']);

    expect($widget->getRequiredPermissions())->toBe(['admin', 'view-widgets']);
});

test('getRequiredPermissions() returns set permissions', function (): void {
    $widget = makeStatWidget('get-perms');

    expect($widget->getRequiredPermissions())->toBe([]);

    $widget->permissions(['read', 'write']);

    expect($widget->getRequiredPermissions())->toBe(['read', 'write']);
});

it('exportAs unknown format throws InvalidArgumentException', function (): void {
    config(['dashboard.cache.enabled' => false]);

    $widget = makeStatWidget('export-unknown');

    $widget->exportAs('unknown');
})->throws(InvalidArgumentException::class, 'Unsupported export format: unknown');

it('stat data exports as CSV with headers', function (): void {
    config(['dashboard.cache.enabled' => false]);

    // Bind WidgetContext so exportAsCsv can resolve it
    app()->instance(WidgetContext::class, new WidgetContext);

    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'stat-csv';
        }

        public function label(): string
        {
            return 'Stat CSV';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(100, 80));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 100;
        }
    };

    $csv = $widget->exportAs('csv');

    expect($csv)->toBeString();
    expect($csv)->toContain('Metric');
    expect($csv)->toContain('Value');
    expect($csv)->toContain('Previous');
    expect($csv)->toContain('Change');
    expect($csv)->toContain('Stat CSV');
    expect($csv)->toContain('100');
});

it('table data exports rows as CSV', function (): void {
    config(['dashboard.cache.enabled' => false]);

    app()->instance(WidgetContext::class, new WidgetContext);

    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'table-csv';
        }

        public function label(): string
        {
            return 'Table CSV';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table(
                rows: [
                    ['name' => 'Alice', 'score' => 95],
                    ['name' => 'Bob', 'score' => 87],
                ],
                columns: [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'score', 'label' => 'Score'],
                ],
            ));
        }
    };

    $csv = $widget->exportAs('csv');

    expect($csv)->toBeString();
    expect($csv)->toContain('Name');
    expect($csv)->toContain('Score');
    expect($csv)->toContain('Alice');
    expect($csv)->toContain('95');
    expect($csv)->toContain('Bob');
    expect($csv)->toContain('87');
});

it('series data exports series data as CSV', function (): void {
    config(['dashboard.cache.enabled' => false]);

    app()->instance(WidgetContext::class, new WidgetContext);

    $widget = new class extends AbstractWidget
    {
        public function key(): string
        {
            return 'series-csv';
        }

        public function label(): string
        {
            return 'Series CSV';
        }

        public function type(): WidgetType
        {
            return WidgetType::LINE;
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart(
                series: [
                    ChartSeries::make('Revenue', [100, 200, 300]),
                    ChartSeries::make('Costs', [50, 75, 100]),
                ],
                labels: ['Jan', 'Feb', 'Mar'],
            ));
        }
    };

    $csv = $widget->exportAs('csv');

    expect($csv)->toBeString();
    expect($csv)->toContain('Label');
    expect($csv)->toContain('Revenue');
    expect($csv)->toContain('Costs');
    expect($csv)->toContain('Jan');
    expect($csv)->toContain('200');
    expect($csv)->toContain('75');
});

function makeStatWidget(string $key): StatWidget
{
    return new class($key) extends StatWidget
    {
        public function __construct(private string $widgetKey) {}

        public function key(): string
        {
            return $this->widgetKey;
        }

        public function label(): string
        {
            return 'Test '.$this->widgetKey;
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };
}
