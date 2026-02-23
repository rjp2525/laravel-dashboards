<?php

namespace Reno\Dashboard\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\AbstractWidget;
use RuntimeException;

class ResolveWidgetData
{
    public function __construct(
        protected DashboardManager $manager,
    ) {}

    public function execute(string $widgetKey, WidgetContext $context): WidgetData
    {
        $widget = $this->manager->getWidget($widgetKey);

        if (! $widget instanceof AbstractWidget) {
            throw new RuntimeException("Widget [{$widgetKey}] is not registered.");
        }

        if (! $widget->authorize($context->user)) {
            throw new AuthorizationException("Unauthorized to view widget [{$widgetKey}].");
        }

        return $widget->resolve($context);
    }
}
