<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Actions\ApplyPreset;
use Reno\Dashboard\Actions\CreatePreset;
use Reno\Dashboard\Actions\ExportWidget;
use Reno\Dashboard\Actions\LoadDashboardLayout;
use Reno\Dashboard\Actions\ResolveWidgetData;
use Reno\Dashboard\Actions\SaveDashboardLayout;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Models\DashboardWidget;
use Reno\Dashboard\Models\UserDashboard;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;
use Reno\Dashboard\Tests\Fixtures\TestUser;
use Reno\Dashboard\Widgets\StatWidget;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

test('ResolveWidgetData execute() returns WidgetData for registered widget', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    $action = new ResolveWidgetData($manager);
    $context = new WidgetContext(period: Period::THIRTY_DAYS);

    $result = $action->execute('test-stat', $context);

    expect($result)->toBeInstanceOf(WidgetData::class)
        ->and($result->value)->toBe(100);
});

test('ResolveWidgetData execute() throws RuntimeException for unregistered widget key', function (): void {
    $manager = app(DashboardManager::class);
    $action = new ResolveWidgetData($manager);
    $context = new WidgetContext(period: Period::THIRTY_DAYS);

    $action->execute('non-existent-widget', $context);
})->throws(RuntimeException::class, 'Widget [non-existent-widget] is not registered.');

test('ResolveWidgetData execute() throws AuthorizationException when widget denies access', function (): void {
    $manager = app(DashboardManager::class);

    // Create a widget that explicitly denies access
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'deny-widget';
        }

        public function label(): string
        {
            return 'Deny Widget';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(1));
        }

        protected function resolveCurrentValue($context): mixed
        {
            return 1;
        }

        public function authorize(?Authenticatable $user): bool
        {
            return false;
        }
    };

    $manager->registerInline('deny-widget', $widget);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $action = new ResolveWidgetData($manager);
    $context = new WidgetContext(user: $user, period: Period::THIRTY_DAYS);

    $action->execute('deny-widget', $context);
})->throws(AuthorizationException::class);

test('ExportWidget execute() returns StreamedResponse', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    $action = new ExportWidget($manager);
    $result = $action->execute('test-stat', 'csv');

    expect($result)->toBeInstanceOf(StreamedResponse::class);
});

test('ExportWidget execute() throws RuntimeException for unregistered widget', function (): void {
    $manager = app(DashboardManager::class);
    $action = new ExportWidget($manager);

    $action->execute('non-existent-widget', 'csv');
})->throws(RuntimeException::class, 'Widget [non-existent-widget] is not registered.');

test('SaveDashboardLayout execute() creates UserDashboard record', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test Dashboard', 'slug' => 'save-layout-test']);

    $layout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2]],
    ];

    $action = new SaveDashboardLayout;
    $result = $action->execute($user, 'save-layout-test', $layout);

    expect($result)->toBeInstanceOf(UserDashboard::class)
        ->and($result->layout)->toBe($layout)
        ->and($result->dashboard_id)->toBe($dashboard->id)
        ->and($result->user_id)->toBe($user->id);

    $this->assertDatabaseHas('user_dashboards', [
        'user_type' => TestUser::class,
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
    ]);
});

test('LoadDashboardLayout execute() returns user layout when exists', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'load-user-layout']);

    $userLayout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 3]],
    ];

    UserDashboard::create([
        'user_type' => TestUser::class,
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
        'layout' => $userLayout,
    ]);

    $manager = app(DashboardManager::class);
    $action = new LoadDashboardLayout($manager);
    $result = $action->execute($user, 'load-user-layout');

    expect($result)->toBe($userLayout);
});

test('LoadDashboardLayout execute() returns system preset layout when no user layout', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'load-system-preset']);

    $systemLayout = [
        ['key' => 'test-chart', 'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 4]],
    ];

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Default System',
        'layout' => $systemLayout,
        'is_system' => true,
    ]);

    $manager = app(DashboardManager::class);
    $action = new LoadDashboardLayout($manager);
    $result = $action->execute($user, 'load-system-preset');

    expect($result)->toBe($systemLayout);
});

test('LoadDashboardLayout execute() returns widget defaults when no presets', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'load-widget-defaults']);

    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    // Register widget in the dashboard_widgets table
    DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => TestStatWidget::class,
        'widget_key' => 'test-stat',
        'label' => 'Test Stat',
        'type' => 'stat',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $action = new LoadDashboardLayout($manager);
    $result = $action->execute($user, 'load-widget-defaults');

    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty()
        ->and($result[0]['key'])->toBe('test-stat')
        ->and($result[0]['position'])->toBeArray();
});

test('CreatePreset execute() creates DashboardPreset', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'create-preset-test']);

    $layout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2]],
    ];

    $action = new CreatePreset;
    $result = $action->execute($user, 'create-preset-test', 'My Preset', $layout);

    expect($result)->toBeInstanceOf(DashboardPreset::class)
        ->and($result->name)->toBe('My Preset')
        ->and($result->layout)->toBe($layout)
        ->and($result->dashboard_id)->toBe($dashboard->id)
        ->and($result->created_by_type)->toBe(TestUser::class)
        ->and($result->created_by_id)->toBe($user->id)
        ->and($result->is_system)->toBeFalse();

    $this->assertDatabaseHas('dashboard_presets', [
        'name' => 'My Preset',
        'dashboard_id' => $dashboard->id,
    ]);
});

test('ApplyPreset execute() updates UserDashboard with preset layout', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'apply-preset-test']);

    $presetLayout = [
        ['key' => 'test-chart', 'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 4]],
    ];

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Preset To Apply',
        'layout' => $presetLayout,
        'is_system' => false,
        'created_by_type' => TestUser::class,
        'created_by_id' => $user->id,
    ]);

    $action = new ApplyPreset;
    $result = $action->execute($user, $preset->id);

    expect($result)->toBeInstanceOf(UserDashboard::class)
        ->and($result->layout)->toBe($presetLayout)
        ->and($result->active_preset_id)->toBe($preset->id)
        ->and($result->dashboard_id)->toBe($dashboard->id);

    $this->assertDatabaseHas('user_dashboards', [
        'user_type' => TestUser::class,
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
        'active_preset_id' => $preset->id,
    ]);
});
