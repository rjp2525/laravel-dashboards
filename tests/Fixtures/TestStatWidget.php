<?php

namespace Reno\Dashboard\Tests\Fixtures;

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\StatWidget;

class TestStatWidget extends StatWidget
{
    public function key(): string
    {
        return 'test-stat';
    }

    public function label(): string
    {
        return 'Test Stat';
    }

    public function dataProvider(): DataProvider
    {
        return CallbackDataProvider::from(function ($context): WidgetData {
            return WidgetData::stat(100, 80);
        });
    }

    protected function resolveCurrentValue($context): mixed
    {
        return 100;
    }

    protected function resolvePreviousValue($context): mixed
    {
        return 80;
    }
}
