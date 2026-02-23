<?php

namespace Reno\Dashboard\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Support\WidgetContext;

class DashboardWidget extends Component
{
    public string $widgetKey;

    public string $dashboardSlug;

    public string $period = '30d';

    /** @var array<string, mixed> */
    public array $widgetData = [];

    /** @var array<string, mixed> */
    public array $widgetDefinition = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        /** @var DashboardManager $manager */
        $manager = app(DashboardManager::class);

        $widget = $manager->getWidget($this->widgetKey);

        if (! $widget) {
            return;
        }

        $context = new WidgetContext(
            user: auth()->user(),
            period: Period::tryFrom($this->period) ?? Period::THIRTY_DAYS,
        );

        $data = $manager->resolveWidgetData($this->widgetKey, $context);

        $this->widgetData = $data->toArray();
        $this->widgetDefinition = $widget->toArray();
    }

    #[On('echo:dashboard.{dashboardSlug},.widget.updated')]
    public function handleWidgetUpdate(string $widget_key, mixed $data): void
    {
        if ($widget_key !== $this->widgetKey) {
            return;
        }

        if (is_array($data)) {
            /** @var array<string, mixed> $data */
            $this->widgetData = $data;
        }
    }

    public function getRefreshIntervalProperty(): ?string
    {
        /** @var DashboardManager $manager */
        $manager = app(DashboardManager::class);

        $widget = $manager->getWidget($this->widgetKey);

        if (! $widget) {
            return null;
        }

        $refreshConfig = $widget->refreshStrategy();

        if ($refreshConfig->strategy !== RefreshStrategy::POLL) {
            return null;
        }

        return $refreshConfig->interval.'s';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('dashboard::livewire.widget');
    }
}
