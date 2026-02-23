<?php

namespace Reno\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<int, array<string, mixed>> $resource
 */
class LayoutResource extends JsonResource
{
    /** @return array<int, array<string, mixed>> */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
