<?php

namespace Reno\Dashboard\Widgets\Traits;

use InvalidArgumentException;
use Reno\Dashboard\Support\ChartSeries;
use Reno\Dashboard\Support\WidgetContext;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\AbstractWidget;

/**
 * @phpstan-require-extends AbstractWidget
 */
trait HasExport
{
    public function exportAs(string $format): mixed
    {
        return match ($format) {
            'csv' => $this->exportAsCsv(),
            default => throw new InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    protected function exportAsCsv(): string
    {
        /** @var WidgetContext $context */
        $context = app(WidgetContext::class);
        $data = $this->resolve($context);

        if (! empty($data->rows)) {
            return $this->rowsToCsv($data);
        }

        if (! empty($data->series)) {
            return $this->seriesToCsv($data);
        }

        return $this->statToCsv($data);
    }

    protected function rowsToCsv(WidgetData $data): string
    {
        $output = fopen('php://temp', 'r+');
        assert($output !== false);

        if ($data->columns !== []) {
            $columnHeaders = array_map(
                function (array $col): string {
                    $label = $col['label'] ?? $col['key'] ?? reset($col);

                    return is_string($label) ? $label : '';
                },
                $data->columns
            );
            fputcsv($output, $columnHeaders, escape: '\\');
        } elseif ($data->rows !== []) {
            fputcsv($output, array_keys($data->rows[0]), escape: '\\');
        }

        foreach ($data->rows as $row) {
            $fields = array_values(array_map(
                fn (mixed $v): bool|float|int|string|null => is_bool($v) || is_float($v) || is_int($v) || is_string($v) || $v === null ? $v : null,
                $row
            ));
            fputcsv($output, $fields, escape: '\\');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv !== false ? $csv : '';
    }

    protected function seriesToCsv(WidgetData $data): string
    {
        $output = fopen('php://temp', 'r+');
        assert($output !== false);

        $headers = ['Label'];
        foreach ($data->series as $series) {
            if ($series instanceof ChartSeries) {
                $headers[] = $series->name;
            } else {
                $headers[] = is_string($series['name'] ?? null) ? $series['name'] : 'Series';
            }
        }
        fputcsv($output, $headers, escape: '\\');

        $maxLen = 0;
        foreach ($data->series as $series) {
            if ($series instanceof ChartSeries) {
                $seriesLen = count($series->data);
            } else {
                /** @var array<int, mixed> $seriesData */
                $seriesData = is_array($series['data'] ?? null) ? $series['data'] : [];
                $seriesLen = count($seriesData);
            }
            $maxLen = max($maxLen, $seriesLen);
        }

        for ($i = 0; $i < $maxLen; $i++) {
            /** @var list<bool|float|int|string|null> $row */
            $row = [$data->labels[$i] ?? $i];
            foreach ($data->series as $series) {
                if ($series instanceof ChartSeries) {
                    $row[] = $series->data[$i] ?? '';
                } else {
                    /** @var array<int, mixed> $seriesData */
                    $seriesData = is_array($series['data'] ?? null) ? $series['data'] : [];
                    $cellValue = $seriesData[$i] ?? '';
                    $row[] = is_scalar($cellValue) ? $cellValue : null;
                }
            }
            fputcsv($output, $row, escape: '\\');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv !== false ? $csv : '';
    }

    protected function statToCsv(WidgetData $data): string
    {
        $output = fopen('php://temp', 'r+');
        assert($output !== false);

        fputcsv($output, ['Metric', 'Value', 'Previous', 'Change', 'Change %'], escape: '\\');
        $value = is_bool($data->value) || is_float($data->value) || is_int($data->value) || is_string($data->value) || $data->value === null
            ? $data->value : null;
        $previousValue = is_bool($data->previousValue) || is_float($data->previousValue) || is_int($data->previousValue) || is_string($data->previousValue) || $data->previousValue === null
            ? $data->previousValue : null;
        fputcsv($output, [
            $this->label(),
            $value,
            $previousValue,
            $data->change,
            $data->changePercent,
        ], escape: '\\');

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv !== false ? $csv : '';
    }
}
