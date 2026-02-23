<?php

namespace Reno\Dashboard\Livewire;

use Livewire\Component;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Widgets\AbstractWidget;

class LivewireDashboard extends Component
{
    public string $slug;

    public string $period = '30d';

    /** @var array<int, array<string, mixed>> */
    public array $widgets = [];

    public function mount(?string $slug = null): void
    {
        $dashboard = $slug
            ? Dashboard::where('slug', $slug)->firstOrFail()
            : Dashboard::where('is_default', true)->firstOrFail();

        $this->slug = $dashboard->slug;

        /** @var DashboardManager $manager */
        $manager = app(DashboardManager::class);

        /** @var array<int, array<string, mixed>> $widgets */
        $widgets = collect($manager->getWidgets($this->slug))
            ->filter(fn (AbstractWidget $widget): bool => $widget->authorize(auth()->user()))
            ->map(fn (AbstractWidget $widget): array => $widget->toArray())
            ->values()
            ->toArray();

        $this->widgets = $widgets;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('dashboard::livewire.dashboard');
    }
}
