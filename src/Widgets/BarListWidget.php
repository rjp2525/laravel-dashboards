<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class BarListWidget extends AbstractWidget
{
    protected int $limit = 5;

    public function type(): WidgetType
    {
        return WidgetType::BAR_LIST;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 4, h: 3, minW: 3, minH: 2);
    }

    public function component(): string
    {
        return 'BarListWidget';
    }
}
