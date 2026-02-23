<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Widgets\Traits\HasPeriodComparison;

abstract class StatWidget extends AbstractWidget
{
    use HasPeriodComparison;

    public function type(): WidgetType
    {
        return WidgetType::STAT;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 3, h: 1, minW: 2, minH: 1);
    }

    public function component(): string
    {
        return 'StatWidget';
    }
}
