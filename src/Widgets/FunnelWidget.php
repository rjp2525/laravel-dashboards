<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class FunnelWidget extends AbstractWidget
{
    public function type(): WidgetType
    {
        return WidgetType::FUNNEL;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 6, h: 4, minW: 4, minH: 3);
    }

    public function component(): string
    {
        return 'FunnelWidget';
    }
}
