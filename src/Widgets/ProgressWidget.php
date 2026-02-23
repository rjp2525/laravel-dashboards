<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class ProgressWidget extends AbstractWidget
{
    public function type(): WidgetType
    {
        return WidgetType::PROGRESS;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 3, h: 1, minW: 2, minH: 1);
    }

    public function component(): string
    {
        return 'ProgressWidget';
    }
}
