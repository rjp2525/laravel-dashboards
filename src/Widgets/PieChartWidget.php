<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class PieChartWidget extends AbstractWidget
{
    protected WidgetType $chartType = WidgetType::PIE;

    public function type(): WidgetType
    {
        return $this->chartType;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 4, h: 3, minW: 3, minH: 2);
    }

    public function component(): string
    {
        return 'PieChartWidget';
    }
}
