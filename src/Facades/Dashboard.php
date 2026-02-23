<?php

namespace Reno\Dashboard\Facades;

use Illuminate\Support\Facades\Facade;
use Reno\Dashboard\Contracts\Widget;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\WidgetBuilder;

/**
 * @method static void register(string $widgetClass)
 * @method static WidgetBuilder widget(string $key)
 * @method static array<string, Widget> getWidgets(?string $dashboardSlug = null)
 * @method static WidgetData resolveWidgetData(string $widgetKey, WidgetContext $context)
 * @method static bool canUserViewWidget($user, string $dashboardId, string $widgetKey)
 * @method static void refreshWidget(string $widgetKey)
 * @method static void refreshAll(?string $dashboardSlug = null)
 *
 * @see \Reno\Dashboard\DashboardManager
 */
class Dashboard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DashboardManager::class;
    }
}
