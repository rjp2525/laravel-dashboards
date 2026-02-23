<?php

use Carbon\Carbon;
use Reno\Dashboard\Enums\AclDriver;
use Reno\Dashboard\Enums\ChangeDirection;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Enums\WidgetType;

it('has all 19 WidgetType cases', function (): void {
    expect(WidgetType::cases())->toHaveCount(19);
});

it('WidgetType cases have correct string values', function (WidgetType $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [WidgetType::STAT, 'stat'],
    [WidgetType::LINE, 'line'],
    [WidgetType::BAR, 'bar'],
    [WidgetType::AREA, 'area'],
    [WidgetType::PIE, 'pie'],
    [WidgetType::DONUT, 'donut'],
    [WidgetType::TABLE, 'table'],
    [WidgetType::LISTING, 'list'],
    [WidgetType::PROGRESS, 'progress'],
    [WidgetType::HEATMAP, 'heatmap'],
    [WidgetType::STATUS_TIMELINE, 'status_timeline'],
    [WidgetType::CUSTOM, 'custom'],
    [WidgetType::SPARKLINE, 'sparkline'],
    [WidgetType::PROGRESS_CIRCLE, 'progress_circle'],
    [WidgetType::BAR_LIST, 'bar_list'],
    [WidgetType::FUNNEL, 'funnel'],
    [WidgetType::CATEGORY, 'category'],
    [WidgetType::BUDGET, 'budget'],
    [WidgetType::GAUGE, 'gauge'],
]);

it('has all 4 RefreshStrategy cases', function (): void {
    expect(RefreshStrategy::cases())->toHaveCount(4);
});

it('RefreshStrategy cases have correct string values', function (RefreshStrategy $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [RefreshStrategy::POLL, 'poll'],
    [RefreshStrategy::PUSH, 'push'],
    [RefreshStrategy::INERTIA, 'inertia'],
    [RefreshStrategy::MANUAL, 'manual'],
]);

it('has all 3 ChangeDirection cases', function (): void {
    expect(ChangeDirection::cases())->toHaveCount(3);
});

it('ChangeDirection cases have correct string values', function (ChangeDirection $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [ChangeDirection::POSITIVE, 'positive'],
    [ChangeDirection::NEGATIVE, 'negative'],
    [ChangeDirection::NEUTRAL, 'neutral'],
]);

test('fromChange returns Positive for positive numbers', function (): void {
    expect(ChangeDirection::fromChange(10))->toBe(ChangeDirection::POSITIVE);
    expect(ChangeDirection::fromChange(0.5))->toBe(ChangeDirection::POSITIVE);
});

test('fromChange returns Negative for negative numbers', function (): void {
    expect(ChangeDirection::fromChange(-5))->toBe(ChangeDirection::NEGATIVE);
    expect(ChangeDirection::fromChange(-0.1))->toBe(ChangeDirection::NEGATIVE);
});

test('fromChange returns Neutral for zero', function (): void {
    expect(ChangeDirection::fromChange(0))->toBe(ChangeDirection::NEUTRAL);
    expect(ChangeDirection::fromChange(0.0))->toBe(ChangeDirection::NEUTRAL);
});

test('fromChange returns Neutral for null', function (): void {
    expect(ChangeDirection::fromChange(null))->toBe(ChangeDirection::NEUTRAL);
});

it('has all 3 AclDriver cases', function (): void {
    expect(AclDriver::cases())->toHaveCount(3);
});

it('AclDriver cases have correct string values', function (AclDriver $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [AclDriver::POLICY, 'policy'],
    [AclDriver::SPATIE, 'spatie'],
    [AclDriver::CUSTOM, 'custom'],
]);

it('has all 7 Period cases', function (): void {
    expect(Period::cases())->toHaveCount(7);
});

it('Period cases have correct string values', function (Period $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [Period::TODAY, 'today'],
    [Period::SEVEN_DAYS, '7d'],
    [Period::THIRTY_DAYS, '30d'],
    [Period::NINETY_DAYS, '90d'],
    [Period::YEAR_TO_DATE, 'ytd'],
    [Period::ONE_YEAR, '1y'],
    [Period::CUSTOM, 'custom'],
]);

it('dateRange returns 2-element array with Carbon instances for non-custom periods', function (Period $period): void {
    $range = $period->dateRange();

    expect($range)->toBeArray()
        ->toHaveCount(2);
    expect($range[0])->toBeInstanceOf(Carbon::class);
    expect($range[1])->toBeInstanceOf(Carbon::class);
})->with([
    Period::TODAY,
    Period::SEVEN_DAYS,
    Period::THIRTY_DAYS,
    Period::NINETY_DAYS,
    Period::YEAR_TO_DATE,
    Period::ONE_YEAR,
]);

it('Custom period throws InvalidArgumentException on dateRange', function (): void {
    Period::CUSTOM->dateRange();
})->throws(InvalidArgumentException::class, 'Custom period requires explicit date range.');

it('previousDateRange returns 2-element array with Carbon instances', function (): void {
    $range = Period::SEVEN_DAYS->previousDateRange();

    expect($range)->toBeArray()
        ->toHaveCount(2);
    expect($range[0])->toBeInstanceOf(Carbon::class);
    expect($range[1])->toBeInstanceOf(Carbon::class);
});

it('Today date range starts and ends on the same day', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    [$start, $end] = Period::TODAY->dateRange();

    expect($start->toDateString())->toBe('2025-06-15');
    expect($end->toDateString())->toBe('2025-06-15');

    Carbon::setTestNow();
});

it('SevenDays range spans approximately 6 days', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    [$start, $end] = Period::SEVEN_DAYS->dateRange();

    expect($start->toDateString())->toBe('2025-06-09');
    expect($end->toDateString())->toBe('2025-06-15');

    Carbon::setTestNow();
});

it('YearToDate starts at the start of the year', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    [$start, $end] = Period::YEAR_TO_DATE->dateRange();

    expect($start->toDateString())->toBe('2025-01-01');
    expect($start->format('H:i:s'))->toBe('00:00:00');
    expect($end->toDateString())->toBe('2025-06-15');

    Carbon::setTestNow();
});

it('OneYear spans approximately 365 days', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    [$start, $end] = Period::ONE_YEAR->dateRange();

    $days = $start->diffInDays($end);

    expect($days)->toBeGreaterThanOrEqual(364)
        ->toBeLessThanOrEqual(366);

    Carbon::setTestNow();
});

it('previousDateRange duration roughly matches current period duration', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    [$currentStart, $currentEnd] = Period::THIRTY_DAYS->dateRange();
    [$prevStart, $prevEnd] = Period::THIRTY_DAYS->previousDateRange();

    $currentDuration = $currentStart->diffInSeconds($currentEnd);
    $prevDuration = $prevStart->diffInSeconds($prevEnd);

    expect($prevDuration)->toBe($currentDuration);

    Carbon::setTestNow();
});

it('dateRange respects timezone parameter', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-15 03:00:00', 'UTC'));

    [$start, $end] = Period::TODAY->dateRange('America/New_York');

    expect($start->timezone->getName())->toBe('America/New_York');
    expect($end->timezone->getName())->toBe('America/New_York');

    Carbon::setTestNow();
});
