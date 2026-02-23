<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Enums\WidgetType;

abstract class CustomWidget extends AbstractWidget
{
    public function type(): WidgetType
    {
        return WidgetType::CUSTOM;
    }

    public function component(): string
    {
        return 'CustomWidget';
    }

    public function dataProvider(): DataProvider
    {
        return CallbackDataProvider::from(fn (): null => null);
    }
}
