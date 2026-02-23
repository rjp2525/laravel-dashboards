<?php

namespace Reno\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Dashboard\Widgets\AbstractWidget;

/**
 * @property AbstractWidget $resource
 */
class WidgetDefinitionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
