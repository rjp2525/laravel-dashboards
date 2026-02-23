<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class GaugeWidget extends AbstractWidget
{
    public function type(): WidgetType
    {
        return WidgetType::GAUGE;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 4, h: 3, minW: 3, minH: 2);
    }

    public function component(): string
    {
        return 'GaugeWidget';
    }
}
