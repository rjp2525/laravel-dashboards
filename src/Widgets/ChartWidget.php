<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class ChartWidget extends AbstractWidget
{
    protected WidgetType $chartType = WidgetType::LINE;

    public function type(): WidgetType
    {
        return $this->chartType;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 6, h: 3, minW: 3, minH: 2);
    }

    public function component(): string
    {
        return 'ChartWidget';
    }

    /** @return array<string, mixed> */
    public function chartOptions(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'chart_options' => $this->chartOptions(),
        ]);
    }
}
