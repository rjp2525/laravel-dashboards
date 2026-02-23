<?php

namespace Reno\Dashboard\Widgets\Traits;

use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

trait HasPeriodComparison
{
    use HasDateRange;

    public function resolveWithComparison(WidgetContext $context): WidgetData
    {
        $currentValue = $this->resolveCurrentValue($context);
        $previousValue = $this->resolvePreviousValue($context);

        return WidgetData::stat($currentValue, $previousValue);
    }

    abstract protected function resolveCurrentValue(WidgetContext $context): mixed;

    protected function resolvePreviousValue(WidgetContext $context): mixed
    {
        return null;
    }
}
