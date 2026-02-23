<?php

namespace Reno\Dashboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\AbstractWidget;
use RuntimeException;

class DashboardManager
{
    /** @var array<string, class-string<AbstractWidget>> */
    protected array $widgets = [];

    /** @var array<string, AbstractWidget> */
    protected array $widgetInstances = [];

    public function __construct(
        protected Application $app,
    ) {}

    public function register(string $widgetClass): void
    {
        if (! is_subclass_of($widgetClass, AbstractWidget::class)) {
            throw new InvalidArgumentException("{$widgetClass} must extend ".AbstractWidget::class);
        }

        /** @var AbstractWidget $instance */
        $instance = $this->app->make($widgetClass);
        $this->widgets[$instance->key()] = $widgetClass;
        $this->widgetInstances[$instance->key()] = $instance;
    }

    public function widget(string $key): WidgetBuilder
    {
        return new WidgetBuilder($this, $key);
    }

    public function registerInline(string $key, AbstractWidget $widget): void
    {
        $this->widgets[$key] = get_class($widget);
        $this->widgetInstances[$key] = $widget;
    }

    /** @return array<string, AbstractWidget> */
    public function getWidgets(?string $dashboardSlug = null): array
    {
        if ($dashboardSlug) {
            $dashboard = Dashboard::where('slug', $dashboardSlug)->first();

            if ($dashboard) {
                $registeredKeys = $dashboard->widgets()->where('is_active', true)->pluck('widget_key')->toArray();

                return array_filter(
                    $this->widgetInstances,
                    fn (AbstractWidget $w): bool => in_array($w->key(), $registeredKeys),
                );
            }
        }

        return $this->widgetInstances;
    }

    public function getWidget(string $key): ?AbstractWidget
    {
        return $this->widgetInstances[$key] ?? null;
    }

    public function resolveWidgetData(string $widgetKey, WidgetContext $context): WidgetData
    {
        $widget = $this->getWidget($widgetKey);

        if (! $widget instanceof AbstractWidget) {
            throw new RuntimeException("Widget [{$widgetKey}] is not registered.");
        }

        return $widget->resolve($context);
    }

    public function canUserViewWidget(?Authenticatable $user, string $dashboardId, string $widgetKey): bool
    {
        $widget = $this->getWidget($widgetKey);

        if (! $widget instanceof AbstractWidget) {
            return false;
        }

        return $widget->authorize($user);
    }

    public function refreshWidget(string $widgetKey): void
    {
        $widget = $this->getWidget($widgetKey);

        if ($widget instanceof AbstractWidget) {
            $widget->forgetCache($widget->cacheKey());
        }
    }

    public function refreshAll(?string $dashboardSlug = null): void
    {
        $widgets = $this->getWidgets($dashboardSlug);

        foreach ($widgets as $widget) {
            $widget->forgetCache($widget->cacheKey());
        }
    }

    /** @return array<string, class-string<AbstractWidget>> */
    public function registered(): array
    {
        return $this->widgets;
    }
}
