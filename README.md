# Dynamic dashboards for Laravel

<p align="center">
    <img src=".github/laravel-dashboards.png" alt="Dynamic dashboards for Laravel" />
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rjp2525/laravel-dashboards.svg?style=flat-square)](https://packagist.org/packages/rjp2525/laravel-dashboards)
[![Tests](https://img.shields.io/github/actions/workflow/status/rjp2525/laravel-dashboards/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rjp2525/laravel-dashboards/actions/workflows/run-tests.yml)
[![Fix PHP Code Style](https://img.shields.io/github/actions/workflow/status/rjp2525/laravel-dashboards/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rjp2525/laravel-dashboards/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/rjp2525/laravel-dashboards.svg?style=flat-square)](https://packagist.org/packages/rjp2525/laravel-dashboards)

This package provides a full-featured, customizable dashboard system for Laravel applications. It pairs a powerful PHP backend of widget registration, data providers, caching, ACL, presets, real-time broadcasting with a Vue 3 + Inertia.js + GridStack.js frontend that lets users drag, drop and resize widgets.

<details>
<summary>Table of Contents</summary>

- [Installation](#installation)
- [Usage](#usage)
  - [Registering widgets](#registering-widgets)
  - [Attribute-based widget discovery](#attribute-based-widget-discovery)
  - [Widget types](#widget-types)
  - [Data providers](#data-providers)
  - [Periods and comparison](#periods-and-comparison)
  - [Layouts and presets](#layouts-and-presets)
  - [Authorization](#authorization)
  - [Caching](#caching)
  - [Real-time updates](#real-time-updates)
  - [Livewire integration](#livewire-integration)
  - [Exporting](#exporting)
  - [Multi-tenancy](#multi-tenancy)
  - [API endpoints](#api-endpoints)
  - [Frontend](#frontend)
  - [Error handling](#error-handling)
  - [Configuration](#configuration)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

</details>

Once installed you can create dashboards like this:

```php
use Reno\Dashboard\Facades\Dashboard;
use Reno\Dashboard\Enums\WidgetType;
use App\Models\Order;

Dashboard::widget('revenue')
    ->label('Revenue')
    ->type(WidgetType::STAT)
    ->using(fn ($context) => WidgetData::stat(
        value: Order::whereBetween('created_at', $context->dateRange())->sum('total'),
    ))
    ->pollEvery(30)
    ->cache(300)
    ->register();
```

## Installation

You can install the package via Composer:

```bash
composer require rjp2525/laravel-dashboards
```

Run the install command to publish the config file and run migrations:

```bash
php artisan dashboard:install
```

You can publish the config file manually with:

```bash
php artisan vendor:publish --tag="dashboard-config"
```

And the migrations with:

```bash
php artisan vendor:publish --tag="dashboard-migrations"
```

## Usage

### Registering widgets

The most common way to register widgets is through the fluent builder on the `Dashboard` facade, typically in a service provider:

```php
use Reno\Dashboard\Facades\Dashboard;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\WidgetData;

// Stat widget with a simple callback
Dashboard::widget('total-users')
    ->label('Total Users')
    ->type(WidgetType::STAT)
    ->icon('users')
    ->using(fn ($context) => WidgetData::stat(
        value: User::count(),
    ))
    ->cache(600)
    ->register();

// Chart widget using an Eloquent data provider
Dashboard::widget('signups-chart')
    ->label('Daily Signups')
    ->type(WidgetType::LINE)
    ->provider(
        EloquentDataProvider::for(User::class)
            ->count()
            ->dateColumn('created_at')
    )
    ->position(0, 0, 6, 3)
    ->pollEvery(60)
    ->register();
```

You can also create dedicated widget classes:

```bash
php artisan dashboard:widget RevenueWidget
```

This generates a widget class in `app/Dashboard/Widgets`:

```php
use Reno\Dashboard\Widgets\StatWidget;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\EloquentDataProvider;

class RevenueWidget extends StatWidget
{
    public function key(): string
    {
        return 'revenue';
    }

    public function label(): string
    {
        return 'Revenue';
    }

    public function dataProvider(): DataProvider
    {
        return EloquentDataProvider::for(Order::class)
            ->sum('total')
            ->dateColumn('created_at');
    }
}
```

Register class-based widgets using the manager:

```php
use Reno\Dashboard\Facades\Dashboard;

Dashboard::register(RevenueWidget::class);
```

### Attribute-based widget discovery

For the simplest setup, annotate your Eloquent models or service classes with PHP attributes and the package will auto-discover and register widgets for you.

**Stat widgets on models** — add `#[DashboardStat]` to generate stat widgets backed by `EloquentDataProvider`:

```php
use Reno\Dashboard\Attributes\Dashboardable;
use Reno\Dashboard\Attributes\DashboardStat;

#[Dashboardable(dateColumn: 'ordered_at', dashboard: 'sales')]
#[DashboardStat(label: 'Total Orders', aggregate: 'count')]
#[DashboardStat(label: 'Revenue', aggregate: 'sum', column: 'total_amount', icon: 'currency-dollar')]
class Order extends Model
{
    // ...
}
```

The `#[Dashboardable]` attribute is optional and provides shared defaults (date column, dashboard scope, model scope) that cascade to all `#[DashboardStat]` on the same class. Each stat attribute can override these defaults individually.

Widget keys are auto-generated from the model name and aggregate: `order_count`, `order_sum_total_amount`. You can set a custom key via the `key` parameter.

Supported aggregates: `count`, `sum`, `avg`, `min`, `max`.

**Custom widgets on methods** — use `#[AsWidget]` on a `public static` method that accepts `WidgetContext` and returns `WidgetData`:

```php
use Reno\Dashboard\Attributes\AsWidget;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;

class AnalyticsService
{
    #[AsWidget(key: 'conversion_rate', label: 'Conversion Rate', type: WidgetType::STAT)]
    public static function conversionRate(WidgetContext $context): WidgetData
    {
        $rate = // ... your logic
        return WidgetData::stat(value: $rate);
    }
}
```

**Configuration** — discovery is enabled by default. Configure which directories to scan in `config/dashboard.php`:

```php
'discovery' => [
    'enabled' => true,
    'paths' => [
        app_path('Models'),
        app_path('Widgets'),
    ],
],
```

**Production caching** — for production, cache the discovery manifest to avoid scanning on every request:

```bash
php artisan dashboard:discover-cache
```

Clear the cache during deployment or development:

```bash
php artisan dashboard:discover-clear
```

All attributes support `cacheTtl`, `permissions`, `dashboard`, and `icon` parameters for fine-grained control.

### Widget types

The package ships with the following widget types:

| Type | Class | Description |
|------|-------|-------------|
| `stat` | `StatWidget` | Single number with change indicator |
| `line` | `ChartWidget` | Line chart |
| `bar` | `ChartWidget` | Bar chart |
| `area` | `ChartWidget` | Area chart |
| `pie` | `PieChartWidget` | Pie chart |
| `donut` | `PieChartWidget` | Donut chart |
| `table` | `TableWidget` | Data table with pagination |
| `listing` | `ListWidget` | Simple list of items |
| `progress` | `ProgressWidget` | Progress bar |
| `heatmap` | `HeatmapWidget` | GitHub-style contribution heatmap |
| `status_timeline` | `StatusTimelineWidget` | Service uptime timeline |
| `sparkline` | `SparklineWidget` | Stat with inline sparkline chart |
| `progress_circle` | `ProgressCircleWidget` | Circular/radial progress indicator |
| `bar_list` | `BarListWidget` | Ranked horizontal bar list |
| `funnel` | `FunnelWidget` | Conversion funnel visualization |
| `category` | `CategoryWidget` | Category breakdown display |
| `budget` | `BudgetWidget` | Budget vs. actual comparison |
| `gauge` | `GaugeWidget` | Gauge/dial meter |
| `custom` | `CustomWidget` | Your own Vue component |

### Data providers

Data providers encapsulate how widget data is fetched. The package includes several built-in providers:

**EloquentDataProvider** — query Eloquent models with automatic date scoping:

```php
EloquentDataProvider::for(Order::class)
    ->sum('total')
    ->dateColumn('created_at')
    ->scope('completed')
    ->query(fn ($query, $context) => $query->where('region', $context->filters['region'] ?? null));
```

**QueryBuilderDataProvider** — raw query builder for complex queries:

```php
QueryBuilderDataProvider::for('analytics_events', 'analytics')
    ->count()
    ->dateColumn('occurred_at');
```

**CallbackDataProvider** — simple closure for quick widgets:

```php
CallbackDataProvider::from(fn ($context) => WidgetData::stat(value: 42));
```

**ApiDataProvider** — fetch data from external APIs:

```php
ApiDataProvider::from('https://api.example.com/metrics')
    ->headers(['Authorization' => 'Bearer ' . config('services.metrics.token')])
    ->timeout(10)
    ->transform(fn ($response, $context) => WidgetData::stat(value: $response['total']));
```

**RawSqlDataProvider** — escape hatch for raw SQL:

```php
RawSqlDataProvider::from('SELECT COUNT(*) as total FROM orders WHERE created_at BETWEEN ? AND ?')
    ->bindingsFrom(fn ($context) => $context->dateRange());
```

### Periods and comparison

Widgets automatically support period-based filtering and comparison. The available periods are:

- `today`, `7d`, `30d`, `90d`, `ytd`, `1y`, `custom`

Period comparison calculates the change between the current and previous period:

```php
$data = WidgetData::stat(
    value: 1500,
    previousValue: 1200,
);

$data->change;          // 300
$data->changePercent;   // 25.0
$data->changeDirection; // ChangeDirection::POSITIVE
```

### Layouts and presets

Each user gets their own dashboard layout stored in the database. Users can drag, drop, and resize widgets to customize their view.

**Presets** let administrators define reusable layouts:

```php
// Create a system preset via artisan
php artisan dashboard:preset create --dashboard=main --name="Executive View" --system
```

Or programmatically:

```php
use Reno\Dashboard\Actions\CreatePreset;
use Reno\Dashboard\Actions\ApplyPreset;

// Create a preset
$preset = (new CreatePreset())->execute($user, 'main', 'My Layout', $layoutArray);

// Apply a preset to a user's dashboard
(new ApplyPreset())->execute($user, $preset->id);
```

Layout resolution priority:
1. User's saved layout
2. Active preset layout
3. System preset layout
4. Widget default positions

### Authorization

The package includes a pluggable ACL system with three built-in drivers:

**Policy driver** (default) — uses each widget's `authorize()` method:

```php
class RevenueWidget extends StatWidget
{
    public function authorize(?Authenticatable $user): bool
    {
        return $user?->hasRole('manager');
    }
}
```

**Spatie driver** — integrates with [spatie/laravel-permission](https://github.com/spatie/laravel-permission):

```php
// config/dashboard.php
'acl' => [
    'driver' => 'spatie',
],

// Widget registration
Dashboard::widget('revenue')
    ->permissions(['view-revenue', 'access-dashboard'])
    ->register();
```

Sync permissions to the Spatie tables:

```bash
php artisan dashboard:permissions
```

**Custom driver** — implement your own:

```php
// config/dashboard.php
'acl' => [
    'driver' => 'custom',
    'custom_driver' => App\Dashboard\MyAclDriver::class,
],
```

Dashboard-level authorization is handled by policies (`DashboardPolicy` and `PresetPolicy`) which control `view`, `editLayout`, `manage`, `create`, `update`, and `delete` actions.

### Caching

Widget data is automatically cached to minimize expensive queries:

```php
// config/dashboard.php
'cache' => [
    'enabled' => true,
    'store' => null,       // null = default store
    'prefix' => 'dashboard',
    'default_ttl' => 300,  // 5 minutes
    'tags_enabled' => false,
],
```

Per-widget cache control:

```php
Dashboard::widget('expensive-report')
    ->cache(3600) // 1 hour
    ->register();
```

Warm the cache for all widgets:

```bash
php artisan dashboard:warm
php artisan dashboard:warm --dashboard=main --period=7d
```

The widget data API endpoints support ETag headers for efficient polling — clients receive `304 Not Modified` when data hasn't changed.

### Real-time updates

The package supports four refresh strategies that control how widgets receive updated data:

| Strategy | Description |
|----------|-------------|
| `poll` | Default. Fetches widget data via HTTP on a timer (`setInterval` + fetch). |
| `push` | Listens for server-sent events via Laravel Echo (Reverb, Pusher, Ably, etc.). |
| `inertia` | Uses Inertia.js partial reloads to refresh widget props on a timer. |
| `manual` | No automatic refresh. Data is only loaded on initial mount or explicit call. |

#### Setting the refresh strategy per widget

Use the fluent builder or dedicated widget class methods:

```php
// Poll every 30 seconds (default strategy)
Dashboard::widget('active-users')
    ->pollEvery(30)
    ->register();

// Push updates via broadcasting
Dashboard::widget('live-orders')
    ->pushUpdates()
    ->register();

// Inertia partial reload every 60 seconds
Dashboard::widget('revenue')
    ->inertiaPolling(60)
    ->register();

// Manual refresh only
Dashboard::widget('annual-report')
    ->manualRefresh()
    ->register();
```

Or use `refreshUsing()` with the `RefreshStrategy` enum for full control:

```php
use Reno\Dashboard\Enums\RefreshStrategy;

Dashboard::widget('stats')
    ->refreshUsing(RefreshStrategy::INERTIA, interval: 45)
    ->register();
```

#### Global realtime adapter

The `realtime.adapter` config controls the default polling mechanism on the frontend. When set to `'inertia'`, all `poll` strategy widgets are automatically upgraded to use Inertia partial reloads instead of HTTP fetch:

```php
// config/dashboard.php
'realtime' => [
    'adapter' => 'inertia', // 'fetch' (default) or 'inertia'
],
```

#### Push updates with broadcasting

Enable broadcasting to push widget updates to connected clients via Laravel Echo:

```php
// config/dashboard.php
'broadcasting' => [
    'enabled' => true,
    'channel_prefix' => 'dashboard',
],
```

The package dispatches three broadcast events:

- `WidgetDataUpdated` — when widget data changes
- `DashboardSaved` — when a user saves their layout
- `PresetApplied` — when a preset is applied

Trigger updates from your application code:

```php
use Reno\Dashboard\Jobs\RefreshWidgetCache;

// Dispatch after an order is placed
RefreshWidgetCache::dispatch('revenue', 'main', '30d');
```

**Frontend setup** — the package reads `window.Echo` at runtime. Configure Laravel Echo in your app's bootstrap file as you normally would (the package does not bundle `laravel-echo`):

```ts
// resources/js/bootstrap.ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'reverb', // or 'pusher', 'ably', etc.
    // ...your config
});
```

All widgets on the same dashboard share a single channel subscription (`dashboard.{slug}`). The `useEcho` composable filters incoming events by `widget_key` so each widget only reacts to its own updates.

If `window.Echo` is not available, the composable logs a warning and the widget falls back gracefully (no crash).

#### Inertia polling adapter

When a widget uses the `inertia` strategy (or the global adapter is set to `'inertia'`), the frontend uses `router.reload()` from `@inertiajs/vue3` with `only: ['widgets']` to perform a partial page reload. This is useful when you want to leverage Inertia's server-side data hydration instead of separate API calls.

The `@inertiajs/vue3` package is dynamically imported at runtime, so it's not required if you don't use this strategy.

#### Using the composables directly

The package exports `useEcho` and `useInertiaPolling` composables for advanced use cases:

```ts
import { useEcho, useInertiaPolling } from '@rjp2525/laravel-dashboards';

// Listen for push updates on a specific dashboard
const { connected, disconnect } = useEcho('main', 'dashboard', (widgetKey, data) => {
    console.log(`Widget ${widgetKey} updated:`, data);
});

// Start Inertia partial reloads every 30 seconds
const { start, stop } = useInertiaPolling(30);
start();
```

### Livewire integration

For Livewire-based applications, the package includes two Livewire 3 components that provide the same widget rendering and real-time update capabilities without requiring Vue or Inertia.

**Requirements** — install Livewire 3 in your application:

```bash
composer require livewire/livewire "^3.0"
```

The package auto-detects Livewire and registers the components automatically. No manual registration is needed.

#### Full dashboard component

Render an entire dashboard with all its authorized widgets:

```blade
<livewire:livewire-dashboard :slug="'main'" :period="'30d'" />
```

#### Individual widget component

Render a single widget anywhere in your Blade templates:

```blade
<livewire:dashboard-widget
    widget-key="revenue"
    dashboard-slug="main"
    period="30d"
/>
```

#### Automatic polling

Widgets using the `poll` strategy automatically include `wire:poll` with the configured interval. Push strategy widgets listen for Echo events via Livewire's `#[On]` attribute — updates broadcast on `dashboard.{slug}` are automatically received and filtered by widget key.

#### Publishing views

To customize the Livewire Blade templates:

```bash
php artisan vendor:publish --tag="dashboard-views"
```

This publishes the templates to `resources/views/vendor/dashboard/livewire/`.

### Exporting

Widgets can be exported to CSV:

```php
// config/dashboard.php
'export' => [
    'enabled' => true,
    'formats' => ['csv'],
    'max_rows' => 10000,
],
```

The export adapts to the widget type — stat widgets export as a single row, chart widgets export series data, and table widgets export all rows.

### Multi-tenancy

For multi-tenant applications, the package can automatically scope widget data:

```php
// config/dashboard.php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\Dashboard\TenantResolver::class,
    'column' => 'tenant_id',
],
```

Data providers automatically filter by the resolved tenant when tenancy is enabled.

### API endpoints

The package registers the following API routes (configurable prefix, default `api/dashboard`):

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/widgets/{key}/data` | Fetch widget data |
| `POST` | `/widgets/batch` | Fetch multiple widgets in one request |
| `GET` | `/widgets/{key}/export` | Export widget data |
| `GET` | `/{slug}/layout` | Load user layout |
| `PUT` | `/{slug}/layout` | Save user layout |
| `GET` | `/{slug}/presets` | List presets |
| `POST` | `/{slug}/presets` | Create preset |
| `GET` | `/{slug}/presets/{id}` | Show preset |
| `PUT` | `/{slug}/presets/{id}` | Update preset |
| `DELETE` | `/{slug}/presets/{id}` | Delete preset |
| `POST` | `/{slug}/presets/{id}/apply` | Apply preset |

A web route serves the Inertia dashboard page:

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/dashboard/{slug?}` | Dashboard page |

### Frontend

The package includes a Vue 3 + TypeScript frontend built with [GridStack.js](https://gridstackjs.com) for the grid layout and [ECharts](https://echarts.apache.org) for charts.

**Vue components:**

- `Dashboard` — root grid container
- `DashboardToolbar` — period selector, edit toggle, preset picker
- `WidgetWrapper` — widget container with header and error boundary
- `WidgetPicker` — sidebar to add widgets in edit mode
- `PresetManager` — save, load, and share presets
- `PeriodSelector` — date range picker
- `StatWidget`, `ChartWidget`, `PieChartWidget`, `TableWidget`, `ListWidget`, `ProgressWidget`, `CustomWidget`

**Composables:**

- `useDashboard()` — dashboard state, editing mode, layout management, `broadcastingEnabled` and `realtimeAdapter` refs
- `useWidget(definition)` — widget data, loading, error, refresh with automatic strategy resolution (poll/push/inertia/manual)
- `useWidgetData()` — data fetching with ETag support
- `useGridStack()` — GridStack initialization and events
- `usePeriod()` — period selection state
- `usePermissions()` — permission checks from Inertia shared data
- `useFetchClient()` — centralized fetch wrapper with XSRF injection and error interception
- `useEcho(slug, prefix, callback)` — Echo/Reverb/Pusher listener for push updates
- `useInertiaPolling(interval)` — Inertia.js partial reload polling

**Publishing and styling components:**

The package ships pre-built CSS and JS via `dist/`. Import the stylesheet in your application's entry point:

```ts
// resources/js/app.ts
import '@rjp2525/laravel-dashboards/dist/laravel-dashboards.css';
```

The default styles use CSS custom properties so you can override the theme without editing package files:

```css
:root {
    --dashboard-bg: #ffffff;
    --dashboard-widget-bg: #f9fafb;
    --dashboard-widget-border: #e5e7eb;
    --dashboard-widget-radius: 0.5rem;
    --dashboard-widget-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    --dashboard-text-primary: #111827;
    --dashboard-text-secondary: #6b7280;
    --dashboard-accent: #3b82f6;
    --dashboard-positive: #10b981;
    --dashboard-negative: #ef4444;
}
```

For dark mode, the package respects `prefers-color-scheme: dark` automatically when the config theme is set to `auto`, or you can force it:

```php
// config/dashboard.php
'frontend' => [
    'theme' => 'dark', // auto, light, dark
],
```

#### Error handling

All internal fetch calls (layout saves, preset operations, widget data) go through a centralized fetch client. You can register global error handlers using `onFetchError` to hook into failures — for example, to show toast notifications or report to an error tracking service:

```ts
import { onFetchError } from '@rjp2525/laravel-dashboards'

// Register a global error handler (e.g. in app.ts)
const unsubscribe = onFetchError((context) => {
    console.error(`Dashboard API error: ${context.method} ${context.url} — ${context.status}`)
    toast.error(`Dashboard error: ${context.statusText}`)
})

// Optionally unsubscribe later
unsubscribe()
```

The `context` object passed to handlers has the following shape:

```ts
interface FetchErrorContext {
    url: string        // Full request URL
    method: string     // HTTP method (GET, POST, PUT, DELETE)
    status?: number    // HTTP status code
    statusText?: string // HTTP status text
    body?: unknown     // Parsed JSON error body (when available)
    error: Error       // The Error instance that will be thrown
}
```

Handlers are called before the error is thrown, so individual callers can still catch errors locally if needed. Multiple handlers can be registered and each receives the same context.

To register a custom Vue widget component, use the `CustomWidget` type and point to your component:

```php
Dashboard::widget('my-custom')
    ->label('My Custom Widget')
    ->type(WidgetType::CUSTOM)
    ->component('MyCustomWidget')
    ->using(fn ($context) => WidgetData::stat(value: 42))
    ->register();
```

Then register the component in your Vue app:

```ts
import MyCustomWidget from './components/MyCustomWidget.vue';

app.component('MyCustomWidget', MyCustomWidget);
```

Your custom component receives `widget` (definition) and `data` (WidgetData) as props.

### Configuration

The full config file (`config/dashboard.php`) covers:

- **Routing** — prefix, middleware, domain
- **Grid** — columns, row height, margin, animation, drag/resize toggles
- **ACL** — driver selection and custom driver class
- **Cache** — store, prefix, TTL, tag support
- **Broadcasting** — enabled, channel prefix, driver
- **Realtime** — frontend polling adapter (`fetch` or `inertia`)
- **Periods** — default period and available options
- **Export** — enabled, formats, max rows
- **Frontend** — chart adapter (`echarts`, `apexcharts`, `chartjs`), theme, locale
- **Presets** — user presets toggle, max per user
- **Tenancy** — enabled, resolver, column
- **Discovery** — attribute scanning toggle and directory paths

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/rjp2525/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Reno Philibert](https://github.com/rjp2525)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
