<?php

namespace Reno\Dashboard\Support;

use DateTimeInterface;
use Reno\Dashboard\Enums\ChangeDirection;

class WidgetData
{
    /**
     * @param  array<int, ChartSeries|array<string, mixed>>  $series
     * @param  array<int, string>  $labels
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly mixed $value = null,
        public readonly mixed $previousValue = null,
        public readonly float|int|null $change = null,
        public readonly ?float $changePercent = null,
        public readonly ChangeDirection $changeDirection = ChangeDirection::NEUTRAL,
        public readonly array $series = [],
        public readonly array $labels = [],
        public readonly array $rows = [],
        public readonly array $columns = [],
        public readonly array $meta = [],
        public readonly ?DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function stat(
        mixed $value,
        mixed $previousValue = null,
        array $meta = [],
    ): self {
        [$change, $changePercent, $direction] = self::computeChange($value, $previousValue);

        return new self(
            value: $value,
            previousValue: $previousValue,
            change: $change,
            changePercent: $changePercent,
            changeDirection: $direction,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, ChartSeries|array<string, mixed>>  $series
     * @param  array<int, string>  $labels
     * @param  array<string, mixed>  $meta
     */
    public static function chart(
        array $series,
        array $labels = [],
        array $meta = [],
    ): self {
        return new self(
            series: $series,
            labels: $labels,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $meta
     */
    public static function table(
        array $rows,
        array $columns = [],
        array $meta = [],
    ): self {
        return new self(
            rows: $rows,
            columns: $columns,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, array{date: string, value: int|float}>  $rows
     * @param  array<string, mixed>  $meta
     */
    public static function heatmap(
        array $rows,
        array $meta = [],
    ): self {
        return new self(
            rows: $rows,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, array{name: string, uptime: float|null, entries: array<int, array{date: string, status: string}>}>  $rows
     * @param  array<string, mixed>  $meta
     */
    public static function statusTimeline(
        array $rows,
        array $meta = [],
    ): self {
        return new self(
            rows: $rows,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, int|float>  $sparkData
     * @param  array<string, mixed>  $meta
     */
    public static function sparkline(
        mixed $value,
        mixed $previousValue = null,
        array $sparkData = [],
        array $meta = [],
    ): self {
        [$change, $changePercent, $direction] = self::computeChange($value, $previousValue);

        return new self(
            value: $value,
            previousValue: $previousValue,
            change: $change,
            changePercent: $changePercent,
            changeDirection: $direction,
            series: [new ChartSeries(name: 'sparkline', data: $sparkData)],
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, array{name: string, value: int|float, href?: string, color?: string}>  $rows
     * @param  array<string, mixed>  $meta
     */
    public static function barList(
        array $rows,
        array $meta = [],
    ): self {
        return new self(
            rows: $rows,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /**
     * @param  array<int, array{name: string, value: int|float}>  $steps
     * @param  array<string, mixed>  $meta
     */
    public static function funnel(
        array $steps,
        array $meta = [],
    ): self {
        return new self(
            rows: $steps,
            meta: $meta,
            updatedAt: now(),
        );
    }

    /** @return array{0: float|int|null, 1: float|null, 2: ChangeDirection} */
    private static function computeChange(mixed $value, mixed $previousValue): array
    {
        if ($previousValue === null || ! is_numeric($value) || ! is_numeric($previousValue)) {
            return [null, null, ChangeDirection::NEUTRAL];
        }

        $numericPrevious = (float) $previousValue;
        $change = $value - $previousValue;
        $changePercent = $numericPrevious != 0
            ? round(($change / abs($numericPrevious)) * 100, 2)
            : null;

        return [$change, $changePercent, ChangeDirection::fromChange($change)];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'previous_value' => $this->previousValue,
            'change' => $this->change,
            'change_percent' => $this->changePercent,
            'change_direction' => $this->changeDirection->value,
            'series' => array_map(
                fn (array|ChartSeries $s): array => $s instanceof ChartSeries ? $s->toArray() : $s,
                $this->series,
            ),
            'labels' => $this->labels,
            'rows' => $this->rows,
            'columns' => $this->columns,
            'meta' => $this->meta,
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }
}
