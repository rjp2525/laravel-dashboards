<?php

use Reno\Dashboard\Enums\ChangeDirection;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Support\WidgetData;

it('calculates positive change correctly', function (): void {
    $data = WidgetData::stat(120, 100);

    expect($data->change)->toBe(20);
    expect($data->changePercent)->toBe(20.0);
    expect($data->changeDirection)->toBe(ChangeDirection::POSITIVE);
});

it('calculates negative change correctly', function (): void {
    $data = WidgetData::stat(80, 100);

    expect($data->change)->toBe(-20);
    expect($data->changePercent)->toBe(-20.0);
    expect($data->changeDirection)->toBe(ChangeDirection::NEGATIVE);
});

it('handles zero previous value', function (): void {
    $data = WidgetData::stat(100, 0);

    expect($data->change)->toBe(100);
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::POSITIVE);
});

it('handles null previous value', function (): void {
    $data = WidgetData::stat(100);

    expect($data->change)->toBeNull();
    expect($data->changePercent)->toBeNull();
    expect($data->changeDirection)->toBe(ChangeDirection::NEUTRAL);
});

it('handles equal values', function (): void {
    $data = WidgetData::stat(100, 100);

    expect($data->change)->toBe(0);
    expect($data->changePercent)->toBe(0.0);
    expect($data->changeDirection)->toBe(ChangeDirection::NEUTRAL);
});

it('period returns correct date ranges', function (): void {
    $period = Period::THIRTY_DAYS;
    [$start, $end] = $period->dateRange();

    expect($start->diffInDays($end))->toBeBetween(29, 30);
});

it('previous period has same duration as current', function (): void {
    $period = Period::SEVEN_DAYS;
    [$start, $end] = $period->dateRange();
    [$prevStart, $prevEnd] = $period->previousDateRange();

    $currentDuration = $start->diffInSeconds($end);
    $previousDuration = $prevStart->diffInSeconds($prevEnd);

    expect($previousDuration)->toBe($currentDuration);
});
