<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Widgets\Traits\HasPeriodComparison;

abstract class CategoryWidget extends AbstractWidget
{
    use HasPeriodComparison;

    public function type(): WidgetType
    {
        return WidgetType::CATEGORY;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 3, h: 2, minW: 2, minH: 2);
    }

    public function component(): string
    {
        return 'CategoryWidget';
    }
}
