<?php

namespace Reno\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Dashboard\Models\Dashboard;

/** @mixin Dashboard */
class DashboardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'grid_config' => $this->grid_config,
            'is_default' => $this->is_default,
            'sort_order' => $this->sort_order,
        ];
    }
}
