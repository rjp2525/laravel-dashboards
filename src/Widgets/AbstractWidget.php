<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Contracts\Authorizable;
use Reno\Dashboard\Contracts\Cacheable;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Contracts\Refreshable;
use Reno\Dashboard\Contracts\Widget;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\Traits\HasCaching;
use Reno\Dashboard\Widgets\Traits\HasExport;
use Reno\Dashboard\Widgets\Traits\HasPermissions;
use Reno\Dashboard\Widgets\Traits\HasRefreshInterval;

abstract class AbstractWidget implements Authorizable, Cacheable, Refreshable, Widget
{
    use HasCaching;
    use HasExport;
    use HasPermissions;
    use HasRefreshInterval;

    abstract public function key(): string;

    abstract public function label(): string;

    abstract public function type(): WidgetType;

    public function icon(): ?string
    {
        return null;
    }

    public function description(): ?string
    {
        return null;
    }

    public function component(): string
    {
        return match ($this->type()) {
            WidgetType::STAT => 'StatWidget',
            WidgetType::LINE, WidgetType::BAR, WidgetType::AREA => 'ChartWidget',
            WidgetType::PIE, WidgetType::DONUT => 'PieChartWidget',
            WidgetType::TABLE => 'TableWidget',
            WidgetType::LISTING => 'ListWidget',
            WidgetType::PROGRESS => 'ProgressWidget',
            WidgetType::HEATMAP => 'HeatmapWidget',
            WidgetType::STATUS_TIMELINE => 'StatusTimelineWidget',
            WidgetType::CUSTOM => 'CustomWidget',
            WidgetType::SPARKLINE => 'SparklineWidget',
            WidgetType::PROGRESS_CIRCLE => 'ProgressCircleWidget',
            WidgetType::BAR_LIST => 'BarListWidget',
            WidgetType::FUNNEL => 'FunnelWidget',
            WidgetType::CATEGORY => 'CategoryWidget',
            WidgetType::BUDGET => 'BudgetWidget',
            WidgetType::GAUGE => 'GaugeWidget',
        };
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition;
    }

    abstract public function dataProvider(): DataProvider;

    public function resolve(WidgetContext $context): WidgetData
    {
        $cacheKey = $this->buildCacheKey(
            'period', $context->period->value,
            'filters', md5(json_encode($context->filters) ?: ''),
        );

        return $this->getCached($cacheKey, function () use ($context): WidgetData {
            $data = $this->dataProvider()->fetch($context);

            if ($data instanceof WidgetData) {
                return $data;
            }

            return new WidgetData(value: $data);
        });
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'type' => $this->type()->value,
            'icon' => $this->icon(),
            'description' => $this->description(),
            'component' => $this->component(),
            'default_position' => $this->defaultPosition()->toArray(),
            'refresh' => $this->refreshStrategy()->toArray(),
            'filters' => $this->filters(),
        ];
    }
}
