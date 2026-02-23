<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class ProgressCircleWidget extends AbstractWidget
{
    public function type(): WidgetType
    {
        return WidgetType::PROGRESS_CIRCLE;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 3, h: 2, minW: 2, minH: 2);
    }

    public function component(): string
    {
        return 'ProgressCircleWidget';
    }
}
