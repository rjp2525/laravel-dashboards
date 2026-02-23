<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class HeatmapWidget extends AbstractWidget
{
    protected int $weeks = 52;

    public function type(): WidgetType
    {
        return WidgetType::HEATMAP;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 12, h: 3, minW: 6, minH: 2);
    }

    public function component(): string
    {
        return 'HeatmapWidget';
    }

    public function weeks(): int
    {
        return $this->weeks;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'weeks' => $this->weeks(),
        ]);
    }
}
