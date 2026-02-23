<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Reno\Dashboard\Actions\ResolveWidgetData;
use Reno\Dashboard\Support\WidgetContext;
use Throwable;

class BatchWidgetDataController extends Controller
{
    public function __construct(
        protected ResolveWidgetData $resolveData,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'widgets' => ['required', 'array'],
            'widgets.*' => ['required', 'string'],
            'period' => ['nullable', 'string'],
            'filters' => ['nullable', 'array'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ]);

        $context = WidgetContext::fromRequest($request);

        /** @var array<int, string> $widgetKeys */
        $widgetKeys = (array) $request->input('widgets');

        $results = [];

        foreach ($widgetKeys as $widgetKey) {
            try {
                $data = $this->resolveData->execute($widgetKey, $context);
                $results[$widgetKey] = [
                    'status' => 'ok',
                    'data' => $data->toArray(),
                ];
            } catch (Throwable $e) {
                $results[$widgetKey] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json(['data' => $results]);
    }
}
