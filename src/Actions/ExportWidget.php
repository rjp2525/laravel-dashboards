<?php

namespace Reno\Dashboard\Actions;

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Widgets\AbstractWidget;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportWidget
{
    public function __construct(
        protected DashboardManager $manager,
    ) {}

    public function execute(string $widgetKey, string $format = 'csv'): StreamedResponse
    {
        $widget = $this->manager->getWidget($widgetKey);

        if (! $widget instanceof AbstractWidget) {
            throw new RuntimeException("Widget [{$widgetKey}] is not registered.");
        }

        $rawData = $widget->exportAs($format);
        $data = is_string($rawData) ? $rawData : '';
        $filename = "{$widgetKey}-export-".now()->format('Y-m-d-His').".{$format}";

        return new StreamedResponse(function () use ($data): void {
            echo $data;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
