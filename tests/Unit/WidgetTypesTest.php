<?php

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\AbstractWidget;
use Reno\Dashboard\Widgets\BarListWidget;
use Reno\Dashboard\Widgets\BudgetWidget;
use Reno\Dashboard\Widgets\CategoryWidget;
use Reno\Dashboard\Widgets\ChartWidget;
use Reno\Dashboard\Widgets\CustomWidget;
use Reno\Dashboard\Widgets\FunnelWidget;
use Reno\Dashboard\Widgets\GaugeWidget;
use Reno\Dashboard\Widgets\HeatmapWidget;
use Reno\Dashboard\Widgets\ListWidget;
use Reno\Dashboard\Widgets\PieChartWidget;
use Reno\Dashboard\Widgets\ProgressCircleWidget;
use Reno\Dashboard\Widgets\ProgressWidget;
use Reno\Dashboard\Widgets\SparklineWidget;
use Reno\Dashboard\Widgets\StatusTimelineWidget;
use Reno\Dashboard\Widgets\StatWidget;
use Reno\Dashboard\Widgets\TableWidget;

test('StatWidget type() returns WidgetType::STAT', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test-stat';
        }

        public function label(): string
        {
            return 'Test Stat';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    expect($widget->type())->toBe(WidgetType::STAT);
});

test('StatWidget component() returns StatWidget', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test-stat';
        }

        public function label(): string
        {
            return 'Test Stat';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    expect($widget->component())->toBe('StatWidget');
});

test('StatWidget defaultPosition() returns 3x1 with minW=2, minH=1', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test-stat';
        }

        public function label(): string
        {
            return 'Test Stat';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(3);
    expect($pos->h)->toBe(1);
    expect($pos->minW)->toBe(2);
    expect($pos->minH)->toBe(1);
});

test('ChartWidget type() returns the chartType property (default Line)', function (): void {
    $widget = new class extends ChartWidget
    {
        public function key(): string
        {
            return 'test-chart';
        }

        public function label(): string
        {
            return 'Test Chart';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::LINE);
});

test('ChartWidget component() returns ChartWidget', function (): void {
    $widget = new class extends ChartWidget
    {
        public function key(): string
        {
            return 'test-chart';
        }

        public function label(): string
        {
            return 'Test Chart';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    expect($widget->component())->toBe('ChartWidget');
});

test('ChartWidget defaultPosition() returns 6x3 with minW=3, minH=2', function (): void {
    $widget = new class extends ChartWidget
    {
        public function key(): string
        {
            return 'test-chart';
        }

        public function label(): string
        {
            return 'Test Chart';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(6);
    expect($pos->h)->toBe(3);
    expect($pos->minW)->toBe(3);
    expect($pos->minH)->toBe(2);
});

test('ChartWidget chartOptions() returns empty array by default', function (): void {
    $widget = new class extends ChartWidget
    {
        public function key(): string
        {
            return 'test-chart';
        }

        public function label(): string
        {
            return 'Test Chart';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    expect($widget->chartOptions())->toBe([]);
});

test('ChartWidget toArray() includes chart_options', function (): void {
    $widget = new class extends ChartWidget
    {
        public function key(): string
        {
            return 'test-chart';
        }

        public function label(): string
        {
            return 'Test Chart';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }

        public function chartOptions(): array
        {
            return ['stacked' => true];
        }
    };

    $array = $widget->toArray();
    expect($array)->toHaveKey('chart_options');
    expect($array['chart_options'])->toBe(['stacked' => true]);
});

test('PieChartWidget type() returns WidgetType::PIE', function (): void {
    $widget = new class extends PieChartWidget
    {
        public function key(): string
        {
            return 'test-pie';
        }

        public function label(): string
        {
            return 'Test Pie';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::PIE);
});

test('PieChartWidget component() returns PieChartWidget', function (): void {
    $widget = new class extends PieChartWidget
    {
        public function key(): string
        {
            return 'test-pie';
        }

        public function label(): string
        {
            return 'Test Pie';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    expect($widget->component())->toBe('PieChartWidget');
});

test('PieChartWidget defaultPosition() returns 4x3', function (): void {
    $widget = new class extends PieChartWidget
    {
        public function key(): string
        {
            return 'test-pie';
        }

        public function label(): string
        {
            return 'Test Pie';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::chart([]));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(4);
    expect($pos->h)->toBe(3);
});

test('TableWidget type() returns WidgetType::TABLE', function (): void {
    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'test-table';
        }

        public function label(): string
        {
            return 'Test Table';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::TABLE);
});

test('TableWidget component() returns TableWidget', function (): void {
    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'test-table';
        }

        public function label(): string
        {
            return 'Test Table';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table([]));
        }
    };

    expect($widget->component())->toBe('TableWidget');
});

test('TableWidget defaultPosition() returns 6x4', function (): void {
    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'test-table';
        }

        public function label(): string
        {
            return 'Test Table';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table([]));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(6);
    expect($pos->h)->toBe(4);
});

test('TableWidget perPage() returns 10', function (): void {
    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'test-table';
        }

        public function label(): string
        {
            return 'Test Table';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table([]));
        }
    };

    expect($widget->perPage())->toBe(10);
});

test('TableWidget toArray() includes per_page', function (): void {
    $widget = new class extends TableWidget
    {
        public function key(): string
        {
            return 'test-table';
        }

        public function label(): string
        {
            return 'Test Table';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::table([]));
        }
    };

    $array = $widget->toArray();
    expect($array)->toHaveKey('per_page');
    expect($array['per_page'])->toBe(10);
});

test('ListWidget type() returns WidgetType::LISTING', function (): void {
    $widget = new class extends ListWidget
    {
        public function key(): string
        {
            return 'test-list';
        }

        public function label(): string
        {
            return 'Test List';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData);
        }
    };

    expect($widget->type())->toBe(WidgetType::LISTING);
});

test('ListWidget component() returns ListWidget', function (): void {
    $widget = new class extends ListWidget
    {
        public function key(): string
        {
            return 'test-list';
        }

        public function label(): string
        {
            return 'Test List';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData);
        }
    };

    expect($widget->component())->toBe('ListWidget');
});

test('ListWidget defaultPosition() returns 4x3', function (): void {
    $widget = new class extends ListWidget
    {
        public function key(): string
        {
            return 'test-list';
        }

        public function label(): string
        {
            return 'Test List';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData);
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(4);
    expect($pos->h)->toBe(3);
});

test('ListWidget limit() returns 5', function (): void {
    $widget = new class extends ListWidget
    {
        public function key(): string
        {
            return 'test-list';
        }

        public function label(): string
        {
            return 'Test List';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData);
        }
    };

    expect($widget->limit())->toBe(5);
});

test('ProgressWidget type() returns WidgetType::PROGRESS', function (): void {
    $widget = new class extends ProgressWidget
    {
        public function key(): string
        {
            return 'test-progress';
        }

        public function label(): string
        {
            return 'Test Progress';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 75));
        }
    };

    expect($widget->type())->toBe(WidgetType::PROGRESS);
});

test('ProgressWidget component() returns ProgressWidget', function (): void {
    $widget = new class extends ProgressWidget
    {
        public function key(): string
        {
            return 'test-progress';
        }

        public function label(): string
        {
            return 'Test Progress';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 75));
        }
    };

    expect($widget->component())->toBe('ProgressWidget');
});

test('ProgressWidget defaultPosition() returns 3x1', function (): void {
    $widget = new class extends ProgressWidget
    {
        public function key(): string
        {
            return 'test-progress';
        }

        public function label(): string
        {
            return 'Test Progress';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 75));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(3);
    expect($pos->h)->toBe(1);
});

test('HeatmapWidget type() returns WidgetType::HEATMAP', function (): void {
    $widget = new class extends HeatmapWidget
    {
        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::HEATMAP);
});

test('HeatmapWidget component() returns HeatmapWidget', function (): void {
    $widget = new class extends HeatmapWidget
    {
        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    expect($widget->component())->toBe('HeatmapWidget');
});

test('HeatmapWidget defaultPosition() returns 12x3 with minW=6, minH=2', function (): void {
    $widget = new class extends HeatmapWidget
    {
        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(12);
    expect($pos->h)->toBe(3);
    expect($pos->minW)->toBe(6);
    expect($pos->minH)->toBe(2);
});

test('HeatmapWidget weeks() returns 52 by default', function (): void {
    $widget = new class extends HeatmapWidget
    {
        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    expect($widget->weeks())->toBe(52);
});

test('HeatmapWidget weeks() respects custom value', function (): void {
    $widget = new class extends HeatmapWidget
    {
        protected int $weeks = 26;

        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    expect($widget->weeks())->toBe(26);
});

test('HeatmapWidget toArray() includes weeks', function (): void {
    $widget = new class extends HeatmapWidget
    {
        public function key(): string
        {
            return 'test-heatmap';
        }

        public function label(): string
        {
            return 'Test Heatmap';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::heatmap([]));
        }
    };

    $array = $widget->toArray();
    expect($array)->toHaveKey('weeks');
    expect($array['weeks'])->toBe(52);
});

test('StatusTimelineWidget type() returns WidgetType::STATUS_TIMELINE', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::STATUS_TIMELINE);
});

test('StatusTimelineWidget component() returns StatusTimelineWidget', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    expect($widget->component())->toBe('StatusTimelineWidget');
});

test('StatusTimelineWidget defaultPosition() returns 12x2 with minW=6, minH=1', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    $pos = $widget->defaultPosition();
    expect($pos->w)->toBe(12);
    expect($pos->h)->toBe(2);
    expect($pos->minW)->toBe(6);
    expect($pos->minH)->toBe(1);
});

test('StatusTimelineWidget days() returns 90 by default', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    expect($widget->days())->toBe(90);
});

test('StatusTimelineWidget days() respects custom value', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        protected int $days = 30;

        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    expect($widget->days())->toBe(30);
});

test('StatusTimelineWidget toArray() includes days', function (): void {
    $widget = new class extends StatusTimelineWidget
    {
        public function key(): string
        {
            return 'test-status';
        }

        public function label(): string
        {
            return 'Test Status';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::statusTimeline([]));
        }
    };

    $array = $widget->toArray();
    expect($array)->toHaveKey('days');
    expect($array['days'])->toBe(90);
});

test('CustomWidget type() returns WidgetType::CUSTOM', function (): void {
    $widget = new class extends CustomWidget
    {
        public function key(): string
        {
            return 'test-custom';
        }

        public function label(): string
        {
            return 'Test Custom';
        }

        public function component(): string
        {
            return 'MyCustomComponent';
        }
    };

    expect($widget->type())->toBe(WidgetType::CUSTOM);
});

test('CustomWidget dataProvider() returns CallbackDataProvider', function (): void {
    $widget = new class extends CustomWidget
    {
        public function key(): string
        {
            return 'test-custom';
        }

        public function label(): string
        {
            return 'Test Custom';
        }

        public function component(): string
        {
            return 'MyCustomComponent';
        }
    };

    expect($widget->dataProvider())->toBeInstanceOf(CallbackDataProvider::class);
});

test('AbstractWidget component() returns correct component name based on type', function (WidgetType $type, string $expected): void {
    $widget = new class($type) extends AbstractWidget
    {
        public function __construct(private WidgetType $widgetType) {}

        public function key(): string
        {
            return 'test';
        }

        public function label(): string
        {
            return 'Test';
        }

        public function type(): WidgetType
        {
            return $this->widgetType;
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData);
        }
    };

    expect($widget->component())->toBe($expected);
})->with([
    [WidgetType::STAT, 'StatWidget'],
    [WidgetType::LINE, 'ChartWidget'],
    [WidgetType::BAR, 'ChartWidget'],
    [WidgetType::AREA, 'ChartWidget'],
    [WidgetType::PIE, 'PieChartWidget'],
    [WidgetType::DONUT, 'PieChartWidget'],
    [WidgetType::TABLE, 'TableWidget'],
    [WidgetType::LISTING, 'ListWidget'],
    [WidgetType::PROGRESS, 'ProgressWidget'],
    [WidgetType::HEATMAP, 'HeatmapWidget'],
    [WidgetType::STATUS_TIMELINE, 'StatusTimelineWidget'],
    [WidgetType::CUSTOM, 'CustomWidget'],
    [WidgetType::SPARKLINE, 'SparklineWidget'],
    [WidgetType::PROGRESS_CIRCLE, 'ProgressCircleWidget'],
    [WidgetType::BAR_LIST, 'BarListWidget'],
    [WidgetType::FUNNEL, 'FunnelWidget'],
    [WidgetType::CATEGORY, 'CategoryWidget'],
    [WidgetType::BUDGET, 'BudgetWidget'],
    [WidgetType::GAUGE, 'GaugeWidget'],
]);

test('AbstractWidget filters() returns empty array by default', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test';
        }

        public function label(): string
        {
            return 'Test';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    expect($widget->filters())->toBe([]);
});

test('AbstractWidget toArray() returns all expected keys', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test-widget';
        }

        public function label(): string
        {
            return 'Test Widget';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(42));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    $array = $widget->toArray();

    expect($array)->toHaveKeys([
        'key', 'label', 'type', 'icon', 'description',
        'component', 'default_position', 'refresh', 'filters',
    ]);
    expect($array['key'])->toBe('test-widget');
    expect($array['label'])->toBe('Test Widget');
    expect($array['type'])->toBe('stat');
    expect($array['icon'])->toBeNull();
    expect($array['description'])->toBeNull();
    expect($array['component'])->toBe('StatWidget');
});

test('AbstractWidget resolve() calls dataProvider and returns WidgetData', function (): void {
    $widget = new class extends StatWidget
    {
        public function key(): string
        {
            return 'test-resolve';
        }

        public function label(): string
        {
            return 'Test Resolve';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(99, 50));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 99;
        }
    };

    config(['dashboard.cache.enabled' => false]);

    $context = new WidgetContext;
    $result = $widget->resolve($context);

    expect($result)->toBeInstanceOf(WidgetData::class);
    expect($result->value)->toBe(99);
    expect($result->previousValue)->toBe(50);
});

test('AbstractWidget resolve() wraps non-WidgetData in WidgetData', function (): void {
    $widget = new class extends AbstractWidget
    {
        public function key(): string
        {
            return 'test-wrap';
        }

        public function label(): string
        {
            return 'Test Wrap';
        }

        public function type(): WidgetType
        {
            return WidgetType::STAT;
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): string => 'raw-value');
        }
    };

    config(['dashboard.cache.enabled' => false]);

    $context = new WidgetContext;
    $result = $widget->resolve($context);

    expect($result)->toBeInstanceOf(WidgetData::class);
    expect($result->value)->toBe('raw-value');
});

test('SparklineWidget type() returns WidgetType::SPARKLINE', function (): void {
    $widget = new class extends SparklineWidget
    {
        public function key(): string
        {
            return 'test-sparkline';
        }

        public function label(): string
        {
            return 'Test Sparkline';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::sparkline(42, 30, [10, 20, 30, 42]));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 42;
        }
    };

    expect($widget->type())->toBe(WidgetType::SPARKLINE);
    expect($widget->component())->toBe('SparklineWidget');
    expect($widget->defaultPosition()->w)->toBe(3);
    expect($widget->defaultPosition()->h)->toBe(2);
    expect($widget->defaultPosition()->minW)->toBe(2);
    expect($widget->defaultPosition()->minH)->toBe(1);
});

test('ProgressCircleWidget type() returns WidgetType::PROGRESS_CIRCLE', function (): void {
    $widget = new class extends ProgressCircleWidget
    {
        public function key(): string
        {
            return 'test-progress-circle';
        }

        public function label(): string
        {
            return 'Test Progress Circle';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 75));
        }
    };

    expect($widget->type())->toBe(WidgetType::PROGRESS_CIRCLE);
    expect($widget->component())->toBe('ProgressCircleWidget');
    expect($widget->defaultPosition()->w)->toBe(3);
    expect($widget->defaultPosition()->h)->toBe(2);
    expect($widget->defaultPosition()->minW)->toBe(2);
    expect($widget->defaultPosition()->minH)->toBe(2);
});

test('BarListWidget type() returns WidgetType::BAR_LIST', function (): void {
    $widget = new class extends BarListWidget
    {
        public function key(): string
        {
            return 'test-bar-list';
        }

        public function label(): string
        {
            return 'Test Bar List';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::barList([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::BAR_LIST);
    expect($widget->component())->toBe('BarListWidget');
    expect($widget->defaultPosition()->w)->toBe(4);
    expect($widget->defaultPosition()->h)->toBe(3);
    expect($widget->defaultPosition()->minW)->toBe(3);
    expect($widget->defaultPosition()->minH)->toBe(2);
});

test('FunnelWidget type() returns WidgetType::FUNNEL', function (): void {
    $widget = new class extends FunnelWidget
    {
        public function key(): string
        {
            return 'test-funnel';
        }

        public function label(): string
        {
            return 'Test Funnel';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::funnel([]));
        }
    };

    expect($widget->type())->toBe(WidgetType::FUNNEL);
    expect($widget->component())->toBe('FunnelWidget');
    expect($widget->defaultPosition()->w)->toBe(6);
    expect($widget->defaultPosition()->h)->toBe(4);
    expect($widget->defaultPosition()->minW)->toBe(4);
    expect($widget->defaultPosition()->minH)->toBe(3);
});

test('CategoryWidget type() returns WidgetType::CATEGORY', function (): void {
    $widget = new class extends CategoryWidget
    {
        public function key(): string
        {
            return 'test-category';
        }

        public function label(): string
        {
            return 'Test Category';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 'cat'));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 'cat';
        }
    };

    expect($widget->type())->toBe(WidgetType::CATEGORY);
    expect($widget->component())->toBe('CategoryWidget');
    expect($widget->defaultPosition()->w)->toBe(3);
    expect($widget->defaultPosition()->h)->toBe(2);
    expect($widget->defaultPosition()->minW)->toBe(2);
    expect($widget->defaultPosition()->minH)->toBe(2);
});

test('BudgetWidget type() returns WidgetType::BUDGET', function (): void {
    $widget = new class extends BudgetWidget
    {
        public function key(): string
        {
            return 'test-budget';
        }

        public function label(): string
        {
            return 'Test Budget';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => WidgetData::stat(1000, 800));
        }

        protected function resolveCurrentValue(WidgetContext $context): mixed
        {
            return 1000;
        }
    };

    expect($widget->type())->toBe(WidgetType::BUDGET);
    expect($widget->component())->toBe('BudgetWidget');
    expect($widget->defaultPosition()->w)->toBe(3);
    expect($widget->defaultPosition()->h)->toBe(2);
    expect($widget->defaultPosition()->minW)->toBe(2);
    expect($widget->defaultPosition()->minH)->toBe(1);
});

test('GaugeWidget type() returns WidgetType::GAUGE', function (): void {
    $widget = new class extends GaugeWidget
    {
        public function key(): string
        {
            return 'test-gauge';
        }

        public function label(): string
        {
            return 'Test Gauge';
        }

        public function dataProvider(): DataProvider
        {
            return CallbackDataProvider::from(fn (): WidgetData => new WidgetData(value: 85));
        }
    };

    expect($widget->type())->toBe(WidgetType::GAUGE);
    expect($widget->component())->toBe('GaugeWidget');
    expect($widget->defaultPosition()->w)->toBe(4);
    expect($widget->defaultPosition()->h)->toBe(3);
    expect($widget->defaultPosition()->minW)->toBe(3);
    expect($widget->defaultPosition()->minH)->toBe(2);
});
