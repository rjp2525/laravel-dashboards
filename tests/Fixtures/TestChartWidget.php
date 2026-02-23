<?php

namespace Reno\Dashboard\Tests\Fixtures;

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Support\ChartSeries;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\ChartWidget;

class TestChartWidget extends ChartWidget
{
    public function key(): string
    {
        return 'test-chart';
    }

    public function label(): string
    {
        return 'Test Chart';
    }

    public function dataProvider(): DataProvider
    {
        return CallbackDataProvider::from(function ($context): WidgetData {
            return WidgetData::chart(
                series: [
                    new ChartSeries('Revenue', [100, 200, 300, 400]),
                ],
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
            );
        });
    }
}
