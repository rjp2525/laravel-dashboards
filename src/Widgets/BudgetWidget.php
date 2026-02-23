<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Widgets\Traits\HasPeriodComparison;

abstract class BudgetWidget extends AbstractWidget
{
    use HasPeriodComparison;

    public function type(): WidgetType
    {
        return WidgetType::BUDGET;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 3, h: 2, minW: 2, minH: 1);
    }

    public function component(): string
    {
        return 'BudgetWidget';
    }
}
