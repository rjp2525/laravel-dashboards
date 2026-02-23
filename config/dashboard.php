<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | Here you may configure the routing options for the dashboard, including
    | the URL prefixes, middleware groups, and an optional domain name
    | constraint applied to all package route definitions.
    |
    */
    'routing' => [
        'prefix' => 'dashboard',
        'api_prefix' => 'api/dashboard',
        'middleware' => ['web', 'auth'],
        'api_middleware' => ['api', 'auth'],
        'domain' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Grid Configuration
    |--------------------------------------------------------------------------
    |
    | These values configure the GridStack.js layout engine that powers your
    | dashboard's drag-and-drop widget grid. You may adjust the column
    | count, row height, margins, and interactivity options.
    |
    */
    'grid' => [
        'columns' => 12,
        'row_height' => 100,
        'margin' => 10,
        'cell_height' => 100,
        'animate' => true,
        'float' => false,
        'removable' => true,
        'disable_resize' => false,
        'disable_drag' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    |
    | This option controls which authorization driver is used to determine
    | who may view or manage dashboards. Supported drivers are "policy",
    | "spatie", or a custom class you provide via custom_driver.
    |
    */
    'acl' => [
        'driver' => 'policy', // policy, spatie, custom
        'custom_driver' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the caching behavior for widget data responses.
    | You can specify the cache store, key prefix, default time-to-live,
    | and whether cache tags should be used for granular busting.
    |
    */
    'cache' => [
        'enabled' => true,
        'store' => null, // null = default store
        'prefix' => 'dashboard',
        'default_ttl' => 300, // 5 minutes
        'tags_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | When enabled, dashboard widgets can receive real-time updates through
    | Laravel's broadcasting system. You may customize the channel name
    | prefix and the broadcast driver used for these events.
    |
    */
    'broadcasting' => [
        'enabled' => false,
        'channel_prefix' => 'dashboard',
        'driver' => null, // null = default
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-Time Updates
    |--------------------------------------------------------------------------
    |
    | Configure how polling widgets fetch updated data on the frontend. The
    | "fetch" adapter uses setInterval with an HTTP fetch, while "inertia"
    | leverages Inertia.js partial reloads to refresh widget props.
    |
    */
    'realtime' => [
        'adapter' => 'fetch', // fetch, inertia
    ],

    /*
    |--------------------------------------------------------------------------
    | Period Defaults
    |--------------------------------------------------------------------------
    |
    | These options define the default and available time periods shown in
    | the dashboard period selector. The "default" value is pre-selected
    | when users first load a dashboard without a saved preference.
    |
    */
    'periods' => [
        'default' => '30d',
        'available' => ['today', '7d', '30d', '90d', 'ytd', '1y', 'custom'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the data export feature for dashboard widgets.
    | You may toggle exports on or off, specify the allowed formats,
    | and set a maximum row limit to prevent excessive exports.
    |
    */
    'export' => [
        'enabled' => true,
        'formats' => ['csv'],
        'max_rows' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Configuration
    |--------------------------------------------------------------------------
    |
    | These values control the frontend rendering of your dashboard charts
    | and widgets. You may choose your preferred charting library, set
    | the color theme, and override the application's locale.
    |
    */
    'frontend' => [
        'chart_adapter' => 'echarts', // echarts, apexcharts, chartjs
        'theme' => 'auto', // auto, light, dark
        'locale' => null, // null = app locale
    ],

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | Presets allow users to save and restore custom dashboard layouts and
    | widget configurations. You may toggle this feature and limit the
    | maximum number of presets each user is allowed to create.
    |
    */
    'presets' => [
        'allow_user_presets' => true,
        'max_presets_per_user' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | You may customize the table names used by this package if they conflict
    | with existing tables in your application. You can also swap in your
    | own Eloquent model classes to extend the default behavior.
    |
    */
    'database' => [
        'user_model' => 'App\\Models\\User',
        'tables' => [
            'dashboards' => 'dashboards',
            'dashboard_widgets' => 'dashboard_widgets',
            'dashboard_presets' => 'dashboard_presets',
            'user_dashboards' => 'user_dashboards',
        ],
        'models' => [
            'dashboard' => \Reno\Dashboard\Models\Dashboard::class,
            'dashboard_widget' => \Reno\Dashboard\Models\DashboardWidget::class,
            'dashboard_preset' => \Reno\Dashboard\Models\DashboardPreset::class,
            'user_dashboard' => \Reno\Dashboard\Models\UserDashboard::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | When enabled, dashboards are automatically scoped to the current
    | tenant using the configured resolver class and column name.
    | Leave disabled if your application does not use tenancy.
    |
    */
    'tenancy' => [
        'enabled' => false,
        'resolver' => null, // class that implements tenant resolver
        'column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will scan the configured paths for classes
    | using #[DashboardStat] and #[AsWidget] attributes and automatically
    | register them as dashboard widgets without manual configuration.
    |
    */
    'discovery' => [
        'enabled' => true,
        'paths' => [
            app_path('Models'),
            app_path('Widgets'),
        ],
    ],
];
