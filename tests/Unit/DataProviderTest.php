<?php

use Illuminate\Support\Facades\Http;
use Reno\Dashboard\DataProviders\ApiDataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\DataProviders\EloquentDataProvider;
use Reno\Dashboard\DataProviders\QueryBuilderDataProvider;
use Reno\Dashboard\DataProviders\RawSqlDataProvider;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

test('CallbackDataProvider::from() factory creates instance', function (): void {
    $provider = CallbackDataProvider::from(fn (): string => 'hello');

    expect($provider)->toBeInstanceOf(CallbackDataProvider::class);
});

test('CallbackDataProvider::fetch() passes context to closure', function (): void {
    $receivedContext = null;

    $provider = CallbackDataProvider::from(function (WidgetContext $ctx) use (&$receivedContext): WidgetData {
        $receivedContext = $ctx;

        return WidgetData::stat($ctx->period->value);
    });

    $context = new WidgetContext(period: Period::SEVEN_DAYS);
    $provider->fetch($context);

    expect($receivedContext)->toBe($context);
});

test('CallbackDataProvider::fetch() returns closure result', function (): void {
    $provider = CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42, 30));

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->value)->toBe(42)
        ->and($result->previousValue)->toBe(30);
});

test('EloquentDataProvider::for() factory creates instance', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class);

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fluent count() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->count();

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fluent sum() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->sum('sort_order');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fluent avg() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->avg('sort_order');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fluent min() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->min('sort_order');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fluent max() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->max('sort_order');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider dateColumn() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->dateColumn('updated_at');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider scope() setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->scope('default');

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider query() callback setter works', function (): void {
    $provider = EloquentDataProvider::for(Dashboard::class)->query(function ($query, $context): void {
        $query->where('is_default', true);
    });

    expect($provider)->toBeInstanceOf(EloquentDataProvider::class);
});

test('EloquentDataProvider fetch() returns WidgetData with stat values', function (): void {
    // Create dashboards with known dates within the current 30-day period
    Dashboard::create([
        'name' => 'Test Dashboard 1',
        'slug' => 'test-dash-1',
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);
    Dashboard::create([
        'name' => 'Test Dashboard 2',
        'slug' => 'test-dash-2',
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    $provider = EloquentDataProvider::for(Dashboard::class)->count();

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->value)->toBeGreaterThanOrEqual(2);
});

test('QueryBuilderDataProvider::for() factory creates instance', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards');

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider fluent count() setter works', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards')->count();

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider fluent sum() setter works', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards')->sum('sort_order');

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider fluent avg() setter works', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards')->avg('sort_order');

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider dateColumn() setter works', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards')->dateColumn('updated_at');

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider query() callback setter works', function (): void {
    $provider = QueryBuilderDataProvider::for('dashboards')->query(function ($query, $context): void {
        $query->where('is_default', true);
    });

    expect($provider)->toBeInstanceOf(QueryBuilderDataProvider::class);
});

test('QueryBuilderDataProvider fetch() returns WidgetData', function (): void {
    Dashboard::create([
        'name' => 'QB Test',
        'slug' => 'qb-test',
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ]);

    $provider = QueryBuilderDataProvider::for('dashboards')->count();

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->value)->toBeGreaterThanOrEqual(1);
});

test('RawSqlDataProvider::from() factory creates instance', function (): void {
    $provider = RawSqlDataProvider::from('SELECT 1 as val');

    expect($provider)->toBeInstanceOf(RawSqlDataProvider::class);
});

test('RawSqlDataProvider bindings() sets static bindings', function (): void {
    $provider = RawSqlDataProvider::from('SELECT * FROM dashboards WHERE slug = ?')
        ->bindings(['test']);

    expect($provider)->toBeInstanceOf(RawSqlDataProvider::class);
});

test('RawSqlDataProvider bindingsFrom() sets dynamic bindings', function (): void {
    $provider = RawSqlDataProvider::from('SELECT * FROM dashboards WHERE slug = ?')
        ->bindingsFrom(fn (WidgetContext $ctx): array => ['some-slug']);

    expect($provider)->toBeInstanceOf(RawSqlDataProvider::class);
});

test('RawSqlDataProvider fetch() returns WidgetData with table data', function (): void {
    Dashboard::create([
        'name' => 'Raw Test',
        'slug' => 'raw-test',
    ]);

    $provider = RawSqlDataProvider::from('SELECT name, slug FROM dashboards WHERE slug = ?')
        ->bindings(['raw-test']);

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->rows)->toHaveCount(1)
        ->and($result->rows[0]['slug'])->toBe('raw-test');
});

test('ApiDataProvider::from() factory creates instance', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data');

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider method() setter works', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data')->method('POST');

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider headers() setter works', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data')
        ->headers(['Authorization' => 'Bearer token123']);

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider query() setter works', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data')
        ->query(['page' => 1, 'limit' => 50]);

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider timeout() setter works', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data')->timeout(30);

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider transform() setter works', function (): void {
    $provider = ApiDataProvider::from('https://api.example.com/data')
        ->transform(fn ($data) => $data);

    expect($provider)->toBeInstanceOf(ApiDataProvider::class);
});

test('ApiDataProvider fetch() with mocked Http facade returns data', function (): void {
    Http::fake([
        'https://api.example.com/data*' => Http::response(['value' => 99, 'label' => 'test'], 200),
    ]);

    $provider = ApiDataProvider::from('https://api.example.com/data');

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeArray()
        ->and($result['value'])->toBe(99)
        ->and($result['label'])->toBe('test');
});

test('ApiDataProvider fetch() with POST method', function (): void {
    Http::fake([
        'https://api.example.com/submit' => Http::response(['status' => 'ok'], 200),
    ]);

    $provider = ApiDataProvider::from('https://api.example.com/submit')
        ->method('POST')
        ->query(['action' => 'refresh']);

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('ok');
});

test('ApiDataProvider fetch() with response transformer', function (): void {
    Http::fake([
        'https://api.example.com/data*' => Http::response(['nested' => ['count' => 42]], 200),
    ]);

    $provider = ApiDataProvider::from('https://api.example.com/data')
        ->transform(function (array $data, WidgetContext $ctx): WidgetData {
            return WidgetData::stat($data['nested']['count']);
        });

    $context = new WidgetContext(period: Period::THIRTY_DAYS);
    $result = $provider->fetch($context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->value)->toBe(42);
});
