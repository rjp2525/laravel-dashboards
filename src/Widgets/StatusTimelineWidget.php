<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class StatusTimelineWidget extends AbstractWidget
{
    protected int $days = 90;

    public function type(): WidgetType
    {
        return WidgetType::STATUS_TIMELINE;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 12, h: 2, minW: 6, minH: 1);
    }

    public function component(): string
    {
        return 'StatusTimelineWidget';
    }

    public function days(): int
    {
        return $this->days;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'days' => $this->days(),
        ]);
    }
}
