<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Reno\Dashboard\Actions\ResolveWidgetData;
use Reno\Dashboard\Http\Requests\WidgetDataRequest;
use Reno\Dashboard\Http\Resources\WidgetDataResource;
use Reno\Dashboard\Support\WidgetContext;

class WidgetDataController extends Controller
{
    public function __construct(
        protected ResolveWidgetData $resolveData,
    ) {}

    public function show(WidgetDataRequest $request, string $key): JsonResponse|Response
    {
        $context = WidgetContext::fromRequest($request);
        $data = $this->resolveData->execute($key, $context);

        $response = new WidgetDataResource($data);

        // ETag support for polling efficiency
        $etag = md5((string) json_encode($data->toArray()));

        if ($request->header('If-None-Match') === $etag) {
            return response()->noContent(304);
        }

        return $response->response()->header('ETag', $etag);
    }
}
