<?php

namespace Reno\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Dashboard\Models\DashboardPreset;

/** @mixin DashboardPreset */
class PresetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dashboard_id' => $this->dashboard_id,
            'name' => $this->name,
            'layout' => $this->layout,
            'is_system' => $this->is_system,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
