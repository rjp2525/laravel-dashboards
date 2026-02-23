<?php

namespace Reno\Dashboard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Events\WidgetDataUpdated;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Widgets\AbstractWidget;

class RefreshWidgetCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $widgetKey,
        public string $dashboardSlug,
        public string $period = '30d',
    ) {}

    public function handle(DashboardManager $manager): void
    {
        $widget = $manager->getWidget($this->widgetKey);

        if (! $widget instanceof AbstractWidget) {
            return;
        }

        $widget->forgetCache($widget->cacheKey());

        $context = new WidgetContext(
            period: Period::tryFrom($this->period) ?? Period::THIRTY_DAYS,
        );

        $data = $widget->resolve($context);

        if (config('dashboard.broadcasting.enabled')) {
            WidgetDataUpdated::dispatch(
                $this->dashboardSlug,
                $this->widgetKey,
                $data->toArray(),
            );
        }
    }
}
