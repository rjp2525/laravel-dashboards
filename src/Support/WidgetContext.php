<?php

namespace Reno\Dashboard\Support;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Reno\Dashboard\Enums\Period;

class WidgetContext
{
    /** @param array<string, mixed> $filters */
    public function __construct(
        public readonly ?Authenticatable $user = null,
        public readonly Period $period = Period::THIRTY_DAYS,
        public readonly array $filters = [],
        public readonly ?string $timezone = null,
        public readonly ?string $tenantId = null,
        public readonly ?DateTimeInterface $startDate = null,
        public readonly ?DateTimeInterface $endDate = null,
    ) {}

    /** @return array{0: DateTimeInterface, 1: DateTimeInterface} */
    public function dateRange(): array
    {
        if ($this->startDate && $this->endDate) {
            return [$this->startDate, $this->endDate];
        }

        return $this->period->dateRange($this->timezone);
    }

    /** @return array{0: DateTimeInterface, 1: DateTimeInterface} */
    public function previousDateRange(): array
    {
        if ($this->startDate && $this->endDate) {
            $diff = $this->startDate->diff($this->endDate);
            $prevEnd = DateTime::createFromInterface($this->startDate)->modify('-1 second');
            $prevStart = DateTime::createFromInterface($prevEnd)->sub($diff);

            return [$prevStart, $prevEnd];
        }

        return $this->period->previousDateRange($this->timezone);
    }

    public static function fromRequest(Request $request): self
    {
        $periodRaw = $request->input('period');
        $defaultPeriod = config('dashboard.periods.default', '30d');
        $periodInput = is_string($periodRaw) ? $periodRaw : (is_string($defaultPeriod) ? $defaultPeriod : '30d');

        /** @var array<string, mixed> $filters */
        $filters = (array) $request->input('filters', []);

        $timezoneRaw = $request->input('timezone');
        $timezone = is_string($timezoneRaw) ? $timezoneRaw : null;

        return new self(
            user: $request->user(),
            period: Period::tryFrom($periodInput) ?? Period::THIRTY_DAYS,
            filters: $filters,
            timezone: $timezone,
        );
    }

    public function withPeriod(Period $period): self
    {
        return new self(
            user: $this->user,
            period: $period,
            filters: $this->filters,
            timezone: $this->timezone,
            tenantId: $this->tenantId,
        );
    }

    /** @param array<string, mixed> $filters */
    public function withFilters(array $filters): self
    {
        return new self(
            user: $this->user,
            period: $this->period,
            filters: array_merge($this->filters, $filters),
            timezone: $this->timezone,
            tenantId: $this->tenantId,
            startDate: $this->startDate,
            endDate: $this->endDate,
        );
    }
}
