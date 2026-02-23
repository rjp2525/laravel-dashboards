<?php

namespace Reno\Dashboard\Tests\Fixtures;

use Reno\Dashboard\Attributes\AsWidget;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

class AttributeTestService
{
    #[AsWidget(key: 'custom_metric', label: 'Custom Metric', type: WidgetType::STAT, icon: 'chart-bar')]
    public static function customMetric(WidgetContext $context): WidgetData
    {
        return WidgetData::stat(42, 30);
    }
}
