<?php

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Discovery\AttributeScanner;
use Reno\Dashboard\Discovery\DiscoveredWidget;
use Reno\Dashboard\Discovery\WidgetDiscovery;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Tests\Fixtures\AttributeTestModel;
use Reno\Dashboard\Tests\Fixtures\AttributeTestService;

it('scans a model with DashboardStat attributes', function (): void {
    $scanner = new AttributeScanner;
    $widgets = $scanner->scan(AttributeTestModel::class);

    expect($widgets)->toHaveCount(2);

    $countWidget = $widgets[0];
    expect($countWidget->key)->toBe('attribute_test_model_count');
    expect($countWidget->label)->toBe('Total Orders');
    expect($countWidget->aggregate)->toBe('count');
    expect($countWidget->dateColumn)->toBe('ordered_at');
    expect($countWidget->dashboard)->toBe('sales');
    expect($countWidget->source)->toBe('stat');

    $sumWidget = $widgets[1];
    expect($sumWidget->key)->toBe('attribute_test_model_sum_total_amount');
    expect($sumWidget->label)->toBe('Revenue');
    expect($sumWidget->aggregate)->toBe('sum');
    expect($sumWidget->aggregateColumn)->toBe('total_amount');
    expect($sumWidget->icon)->toBe('currency-dollar');
});

it('scans a service with AsWidget attribute', function (): void {
    $scanner = new AttributeScanner;
    $widgets = $scanner->scan(AttributeTestService::class);

    expect($widgets)->toHaveCount(1);

    $widget = $widgets[0];
    expect($widget->key)->toBe('custom_metric');
    expect($widget->label)->toBe('Custom Metric');
    expect($widget->type)->toBe(WidgetType::STAT);
    expect($widget->source)->toBe('method');
    expect($widget->methodName)->toBe('customMetric');
    expect($widget->icon)->toBe('chart-bar');
});

it('registers a discovered stat widget in the manager', function (): void {
    $manager = app(DashboardManager::class);
    $discovery = new WidgetDiscovery($manager);

    $discovered = DiscoveredWidget::fromStat(
        key: 'test_discovered',
        label: 'Test Discovered',
        className: AttributeTestModel::class,
        aggregate: 'count',
        dateColumn: 'created_at',
    );

    $discovery->registerDiscovered($discovered);

    expect($manager->getWidget('test_discovered'))->not->toBeNull();
    expect($manager->getWidget('test_discovered')->label())->toBe('Test Discovered');
    expect($manager->getWidget('test_discovered')->type())->toBe(WidgetType::STAT);
});

it('registers a discovered method widget in the manager', function (): void {
    $manager = app(DashboardManager::class);
    $discovery = new WidgetDiscovery($manager);

    $discovered = DiscoveredWidget::fromMethod(
        key: 'custom_metric',
        label: 'Custom Metric',
        type: WidgetType::STAT,
        className: AttributeTestService::class,
        methodName: 'customMetric',
    );

    $discovery->registerDiscovered($discovered);

    $widget = $manager->getWidget('custom_metric');
    expect($widget)->not->toBeNull();
    expect($widget->label())->toBe('Custom Metric');
});

it('round-trips DiscoveredWidget through toArray/fromArray', function (): void {
    $original = DiscoveredWidget::fromStat(
        key: 'order_count',
        label: 'Total Orders',
        className: AttributeTestModel::class,
        aggregate: 'count',
        dateColumn: 'ordered_at',
        dashboard: 'sales',
        icon: 'shopping-cart',
        cacheTtl: 600,
        permissions: ['view-orders'],
    );

    $restored = DiscoveredWidget::fromArray($original->toArray());

    expect($restored->key)->toBe($original->key);
    expect($restored->label)->toBe($original->label);
    expect($restored->type)->toBe($original->type);
    expect($restored->source)->toBe($original->source);
    expect($restored->className)->toBe($original->className);
    expect($restored->aggregate)->toBe($original->aggregate);
    expect($restored->dateColumn)->toBe($original->dateColumn);
    expect($restored->dashboard)->toBe($original->dashboard);
    expect($restored->icon)->toBe($original->icon);
    expect($restored->cacheTtl)->toBe($original->cacheTtl);
    expect($restored->permissions)->toBe($original->permissions);
});

it('generates correct auto-keys for stats', function (): void {
    expect(DiscoveredWidget::generateStatKey(AttributeTestModel::class, 'count', null))
        ->toBe('attribute_test_model_count');

    expect(DiscoveredWidget::generateStatKey(AttributeTestModel::class, 'sum', 'total_amount'))
        ->toBe('attribute_test_model_sum_total_amount');

    expect(DiscoveredWidget::generateStatKey(AttributeTestModel::class, 'avg', 'price'))
        ->toBe('attribute_test_model_avg_price');
});

it('cascades Dashboardable defaults to DashboardStat', function (): void {
    $scanner = new AttributeScanner;
    $widgets = $scanner->scan(AttributeTestModel::class);

    // Both widgets should inherit dateColumn and dashboard from #[Dashboardable]
    foreach ($widgets as $widget) {
        expect($widget->dateColumn)->toBe('ordered_at');
        expect($widget->dashboard)->toBe('sales');
    }
});

it('throws for non-public-static AsWidget methods', function (): void {
    // Define a temporary class with a non-static method
    $className = 'Reno\\Dashboard\\Tests\\Fixtures\\InvalidAsWidgetClass';

    if (! class_exists($className)) {
        eval('
            namespace Reno\\Dashboard\\Tests\\Fixtures;
            use Reno\\Dashboard\\Attributes\\AsWidget;
            use Reno\\Dashboard\\Enums\\WidgetType;
            class InvalidAsWidgetClass {
                #[AsWidget(key: "bad", label: "Bad")]
                public function notStatic(): void {}
            }
        ');
    }

    $scanner = new AttributeScanner;
    $scanner->scan($className);
})->throws(RuntimeException::class, 'must be public and static');
