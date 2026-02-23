<?php

use Carbon\Carbon;
use Reno\Dashboard\Enums\ChangeDirection;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Support\ChartSeries;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Support\RefreshConfig;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

test('stat() calculates change, changePercent, and changeDirection correctly', function (): void {
    $data = WidgetData::stat(150, 100);

    expect($data->value)->toBe(150);
    expect($data->previousValue)->toBe(100);
    expect($data->change)->toBe(50);
    expect($data->changePercent)->toBe(50.0);
    expect($data->changeDirection)->toBe(ChangeDirection::POSITIVE);
});

test('stat() calculates negative change correctly', function (): void {
    $data = WidgetData::stat(80, 100);

    expect($data->change)->toBe(-20);
    expect($data->changePercent)->toBe(-20.0);
    expect($data->changeDirection)->toBe(ChangeDirection::NEGATIVE);
});

test('stat() with null previous returns null change', function (): void {
    $data = WidgetData::stat(42);

    expect($data->change)->toBeNull();
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::NEUTRAL);
});

test('stat() with zero previous returns null changePercent', function (): void {
    $data = WidgetData::stat(10, 0);

    expect($data->change)->toEqual(10);
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::POSITIVE);
});

test('stat() with non-numeric values does not crash', function (): void {
    $data = WidgetData::stat('hello', 'world');

    expect($data->value)->toBe('hello');
    expect($data->previousValue)->toBe('world');
    expect($data->change)->toBeNull();
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::NEUTRAL);
});

test('chart() creates with series and labels', function (): void {
    $series = [ChartSeries::make('Revenue', [10, 20, 30])];
    $labels = ['Jan', 'Feb', 'Mar'];

    $data = WidgetData::chart($series, $labels);

    expect($data->series)->toHaveCount(1);
    expect($data->labels)->toBe(['Jan', 'Feb', 'Mar']);
    expect($data->updatedAt)->not->toBeNull();
});

test('table() creates with rows and columns', function (): void {
    $rows = [['name' => 'Alice', 'age' => 30]];
    $columns = [['key' => 'name', 'label' => 'Name'], ['key' => 'age', 'label' => 'Age']];

    $data = WidgetData::table($rows, $columns);

    expect($data->rows)->toHaveCount(1);
    expect($data->columns)->toHaveCount(2);
    expect($data->updatedAt)->not->toBeNull();
});

test('heatmap() creates with date-value rows', function (): void {
    $rows = [
        ['date' => '2026-01-01', 'value' => 5],
        ['date' => '2026-01-02', 'value' => 12],
        ['date' => '2026-01-03', 'value' => 0],
    ];

    $data = WidgetData::heatmap($rows, ['max' => 20]);

    expect($data->rows)->toHaveCount(3);
    expect($data->rows[0])->toBe(['date' => '2026-01-01', 'value' => 5]);
    expect($data->meta)->toBe(['max' => 20]);
    expect($data->updatedAt)->not->toBeNull();
});

test('statusTimeline() creates with service entries', function (): void {
    $rows = [
        [
            'name' => 'API',
            'uptime' => 99.9,
            'entries' => [
                ['date' => '2026-01-01', 'status' => 'operational'],
                ['date' => '2026-01-02', 'status' => 'degraded'],
            ],
        ],
        [
            'name' => 'Web',
            'uptime' => 100.0,
            'entries' => [
                ['date' => '2026-01-01', 'status' => 'operational'],
                ['date' => '2026-01-02', 'status' => 'operational'],
            ],
        ],
    ];

    $data = WidgetData::statusTimeline($rows);

    expect($data->rows)->toHaveCount(2);
    expect($data->rows[0]['name'])->toBe('API');
    expect($data->rows[0]['uptime'])->toBe(99.9);
    expect($data->rows[0]['entries'])->toHaveCount(2);
    expect($data->rows[1]['uptime'])->toBe(100.0);
    expect($data->updatedAt)->not->toBeNull();
});

it('toArray() returns correct keys and values', function (): void {
    $series = [ChartSeries::make('Data', [1, 2, 3])];
    $data = WidgetData::chart($series, ['a', 'b', 'c'], ['source' => 'test']);

    $array = $data->toArray();

    expect($array)->toHaveKeys([
        'value', 'previous_value', 'change', 'change_percent',
        'change_direction', 'series', 'labels', 'rows', 'columns',
        'meta', 'updated_at',
    ]);
    expect($array['labels'])->toBe(['a', 'b', 'c']);
    expect($array['meta'])->toBe(['source' => 'test']);
    expect($array['change_direction'])->toBe('neutral');
});

it('toArray() converts ChartSeries to array', function (): void {
    $series = [ChartSeries::make('Revenue', [10, 20])];
    $data = WidgetData::chart($series);

    $array = $data->toArray();

    expect($array['series'][0])->toBeArray();
    expect($array['series'][0]['name'])->toBe('Revenue');
    expect($array['series'][0]['data'])->toBe([10, 20]);
});

it('WidgetData constructor with all defaults', function (): void {
    $data = new WidgetData;

    expect($data->value)->toBeNull();
    expect($data->previousValue)->toBeNull();
    expect($data->change)->toBeNull();
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::NEUTRAL);
    expect($data->series)->toBe([]);
    expect($data->labels)->toBe([]);
    expect($data->rows)->toBe([]);
    expect($data->columns)->toBe([]);
    expect($data->meta)->toBe([]);
    expect($data->updatedAt)->toBeNull();
});

it('WidgetContext constructor has correct defaults', function (): void {
    $context = new WidgetContext;

    expect($context->user)->toBeNull();
    expect($context->period)->toBe(Period::THIRTY_DAYS);
    expect($context->filters)->toBe([]);
    expect($context->timezone)->toBeNull();
    expect($context->tenantId)->toBeNull();
    expect($context->startDate)->toBeNull();
    expect($context->endDate)->toBeNull();
});

it('dateRange() returns from period when no explicit dates set', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    $context = new WidgetContext(period: Period::TODAY);
    [$start, $end] = $context->dateRange();

    expect($start)->toBeInstanceOf(Carbon::class);
    expect($start->toDateString())->toBe('2025-06-15');
    expect($end->toDateString())->toBe('2025-06-15');

    Carbon::setTestNow();
});

it('dateRange() returns explicit dates when set', function (): void {
    $start = new DateTime('2025-01-01');
    $end = new DateTime('2025-01-31');

    $context = new WidgetContext(startDate: $start, endDate: $end);
    [$rangeStart, $rangeEnd] = $context->dateRange();

    expect($rangeStart)->toBe($start);
    expect($rangeEnd)->toBe($end);
});

it('previousDateRange() from period', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    $context = new WidgetContext(period: Period::SEVEN_DAYS);
    [$prevStart, $prevEnd] = $context->previousDateRange();

    expect($prevStart)->toBeInstanceOf(Carbon::class);
    expect($prevEnd)->toBeInstanceOf(Carbon::class);
    expect($prevEnd->lt($prevStart) === false)->toBeTrue();

    Carbon::setTestNow();
});

it('previousDateRange() from explicit dates', function (): void {
    $start = new DateTime('2025-02-01 00:00:00');
    $end = new DateTime('2025-02-28 23:59:59');

    $context = new WidgetContext(startDate: $start, endDate: $end);
    [$prevStart, $prevEnd] = $context->previousDateRange();

    expect($prevStart)->toBeInstanceOf(DateTime::class);
    expect($prevEnd)->toBeInstanceOf(DateTime::class);
    expect($prevEnd < $start)->toBeTrue();
});

test('withPeriod() returns new instance with changed period', function (): void {
    $original = new WidgetContext(period: Period::TODAY, filters: ['status' => 'active']);
    $modified = $original->withPeriod(Period::SEVEN_DAYS);

    expect($modified)->not->toBe($original);
    expect($modified->period)->toBe(Period::SEVEN_DAYS);
    expect($modified->filters)->toBe(['status' => 'active']);
    expect($original->period)->toBe(Period::TODAY);
});

test('withFilters() merges filters into new instance', function (): void {
    $original = new WidgetContext(filters: ['status' => 'active']);
    $modified = $original->withFilters(['team' => 'engineering']);

    expect($modified)->not->toBe($original);
    expect($modified->filters)->toBe(['status' => 'active', 'team' => 'engineering']);
    expect($original->filters)->toBe(['status' => 'active']);
});

it('ChartSeries constructor sets name and data', function (): void {
    $series = new ChartSeries('Revenue', [100, 200, 300]);

    expect($series->name)->toBe('Revenue');
    expect($series->data)->toBe([100, 200, 300]);
    expect($series->type)->toBeNull();
    expect($series->color)->toBeNull();
});

test('ChartSeries make() static factory', function (): void {
    $series = ChartSeries::make('Users', [1, 2, 3]);

    expect($series->name)->toBe('Users');
    expect($series->data)->toBe([1, 2, 3]);
});

test('ChartSeries withType() returns new instance', function (): void {
    $original = ChartSeries::make('Data', [1, 2]);
    $modified = $original->withType('bar');

    expect($modified)->not->toBe($original);
    expect($modified->type)->toBe('bar');
    expect($modified->name)->toBe('Data');
    expect($modified->data)->toBe([1, 2]);
    expect($original->type)->toBeNull();
});

test('ChartSeries withColor() returns new instance', function (): void {
    $original = ChartSeries::make('Data', [1, 2]);
    $modified = $original->withColor('#ff0000');

    expect($modified)->not->toBe($original);
    expect($modified->color)->toBe('#ff0000');
    expect($modified->name)->toBe('Data');
    expect($original->color)->toBeNull();
});

test('ChartSeries toArray() filters out null values', function (): void {
    $series = ChartSeries::make('Revenue', [10, 20]);
    $array = $series->toArray();

    expect($array)->toBe(['name' => 'Revenue', 'data' => [10, 20]]);
    expect($array)->not->toHaveKey('type');
    expect($array)->not->toHaveKey('color');
});

test('ChartSeries toArray() includes type and color when set', function (): void {
    $series = ChartSeries::make('Revenue', [10, 20])
        ->withType('line')
        ->withColor('#00ff00');
    $array = $series->toArray();

    expect($array)->toBe([
        'name' => 'Revenue',
        'data' => [10, 20],
        'type' => 'line',
        'color' => '#00ff00',
    ]);
});

it('GridPosition constructor with defaults', function (): void {
    $pos = new GridPosition;

    expect($pos->x)->toBe(0);
    expect($pos->y)->toBe(0);
    expect($pos->w)->toBe(4);
    expect($pos->h)->toBe(2);
    expect($pos->minW)->toBeNull();
    expect($pos->maxW)->toBeNull();
    expect($pos->minH)->toBeNull();
    expect($pos->maxH)->toBeNull();
});

test('GridPosition fromArray() creates from associative array', function (): void {
    $pos = GridPosition::fromArray([
        'x' => 2,
        'y' => 3,
        'w' => 6,
        'h' => 4,
        'min_w' => 3,
        'max_w' => 12,
        'min_h' => 2,
        'max_h' => 8,
    ]);

    expect($pos->x)->toBe(2);
    expect($pos->y)->toBe(3);
    expect($pos->w)->toBe(6);
    expect($pos->h)->toBe(4);
    expect($pos->minW)->toBe(3);
    expect($pos->maxW)->toBe(12);
    expect($pos->minH)->toBe(2);
    expect($pos->maxH)->toBe(8);
});

test('GridPosition toArray() serializes correctly', function (): void {
    $pos = new GridPosition(x: 1, y: 2, w: 6, h: 3, minW: 3, minH: 2);
    $array = $pos->toArray();

    expect($array)->toHaveKey('x', 1);
    expect($array)->toHaveKey('y', 2);
    expect($array)->toHaveKey('w', 6);
    expect($array)->toHaveKey('h', 3);
    expect($array)->toHaveKey('min_w', 3);
    expect($array)->toHaveKey('min_h', 2);
    expect($array)->not->toHaveKey('max_w');
    expect($array)->not->toHaveKey('max_h');
});

it('GridPosition min/max constraints preserved', function (): void {
    $pos = new GridPosition(minW: 2, maxW: 10, minH: 1, maxH: 6);

    expect($pos->minW)->toBe(2);
    expect($pos->maxW)->toBe(10);
    expect($pos->minH)->toBe(1);
    expect($pos->maxH)->toBe(6);
});

it('GridPosition round-trip serialization', function (): void {
    $original = new GridPosition(x: 3, y: 5, w: 8, h: 4, minW: 4, maxW: 12, minH: 2, maxH: 6);
    $array = $original->toArray();
    $restored = GridPosition::fromArray($array);

    expect($restored->x)->toBe($original->x);
    expect($restored->y)->toBe($original->y);
    expect($restored->w)->toBe($original->w);
    expect($restored->h)->toBe($original->h);
    expect($restored->minW)->toBe($original->minW);
    expect($restored->maxW)->toBe($original->maxW);
    expect($restored->minH)->toBe($original->minH);
    expect($restored->maxH)->toBe($original->maxH);
});

test('RefreshConfig poll() factory with interval', function (): void {
    $config = RefreshConfig::poll(30);

    expect($config->strategy)->toBe(RefreshStrategy::POLL);
    expect($config->interval)->toBe(30);
});

test('RefreshConfig poll() factory with default interval', function (): void {
    $config = RefreshConfig::poll();

    expect($config->strategy)->toBe(RefreshStrategy::POLL);
    expect($config->interval)->toBe(60);
});

test('RefreshConfig push() factory', function (): void {
    $config = RefreshConfig::push();

    expect($config->strategy)->toBe(RefreshStrategy::PUSH);
    expect($config->interval)->toBe(0);
});

test('RefreshConfig manual() factory', function (): void {
    $config = RefreshConfig::manual();

    expect($config->strategy)->toBe(RefreshStrategy::MANUAL);
    expect($config->interval)->toBe(0);
});

it('RefreshConfig toArray() serialization', function (): void {
    $config = RefreshConfig::poll(45);
    $array = $config->toArray();

    expect($array)->toBe([
        'strategy' => 'poll',
        'interval' => 45,
    ]);
});
