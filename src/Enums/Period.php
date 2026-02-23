<?php

namespace Reno\Dashboard\Enums;

use Carbon\Carbon;
use InvalidArgumentException;

enum Period: string
{
    case TODAY = 'today';
    case SEVEN_DAYS = '7d';
    case THIRTY_DAYS = '30d';
    case NINETY_DAYS = '90d';
    case YEAR_TO_DATE = 'ytd';
    case ONE_YEAR = '1y';
    case CUSTOM = 'custom';

    /** @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon} */
    public function dateRange(?string $timezone = null): array
    {
        $now = $timezone ? Carbon::now($timezone) : Carbon::now();

        return match ($this) {
            self::TODAY => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            self::SEVEN_DAYS => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            self::THIRTY_DAYS => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            self::NINETY_DAYS => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()],
            self::YEAR_TO_DATE => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            self::ONE_YEAR => [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay()],
            self::CUSTOM => throw new InvalidArgumentException('Custom period requires explicit date range.'),
        };
    }

    /** @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon} */
    public function previousDateRange(?string $timezone = null): array
    {
        [$start, $end] = $this->dateRange($timezone);
        $diff = $start->diffInSeconds($end);

        return [
            $start->copy()->subSeconds($diff + 1),
            $start->copy()->subSecond(),
        ];
    }
}
