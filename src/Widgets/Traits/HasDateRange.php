<?php

namespace Reno\Dashboard\Widgets\Traits;

use DateTimeInterface;
use Reno\Dashboard\Support\WidgetContext;

trait HasDateRange
{
    /** @return array{0: DateTimeInterface, 1: DateTimeInterface} */
    public function resolveDateRange(WidgetContext $context): array
    {
        return $context->dateRange();
    }

    /** @return array{0: DateTimeInterface, 1: DateTimeInterface} */
    public function resolvePreviousDateRange(WidgetContext $context): array
    {
        return $context->previousDateRange();
    }
}
