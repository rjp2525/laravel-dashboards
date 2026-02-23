<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Reno\Dashboard\Actions\LoadDashboardLayout;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Http\Resources\DashboardResource;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Widgets\AbstractWidget;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardManager $manager,
        protected LoadDashboardLayout $loadLayout,
    ) {}

    public function show(Request $request, ?string $slug = null): Response
    {
        $dashboard = $slug
            ? Dashboard::where('slug', $slug)->firstOrFail()
            : Dashboard::where('is_default', true)->firstOrFail();

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $widgets = $this->manager->getWidgets($dashboard->slug);

        $layout = $this->loadLayout->execute($user, $dashboard->slug);

        $widgetDefinitions = collect($widgets)
            ->filter(fn ($widget): bool => $widget->authorize($user))
            ->map(fn (AbstractWidget $widget): array => $widget->toArray())
            ->values()
            ->toArray();

        return Inertia::render('Dashboard/Show', [
            'dashboard' => new DashboardResource($dashboard),
            'widgets' => $widgetDefinitions,
            'layout' => $layout,
            'config' => [
                'grid' => config('dashboard.grid'),
                'periods' => config('dashboard.periods'),
                'broadcasting' => [
                    'enabled' => config('dashboard.broadcasting.enabled'),
                ],
                'realtime' => [
                    'adapter' => config('dashboard.realtime.adapter', 'fetch'),
                ],
                'routing' => [
                    'api_prefix' => '/'.ltrim(config()->string('dashboard.routing.api_prefix', 'api/dashboard'), '/'),
                ],
            ],
        ]);
    }
}
