<?php

namespace Reno\Dashboard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_type
 * @property string|int $user_id
 * @property string $dashboard_id
 * @property array<int, array<string, mixed>>|null $layout
 * @property string|null $active_preset_id
 * @property array<string, mixed>|null $filters
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dashboard $dashboard
 * @property-read DashboardPreset|null $activePreset
 */
class UserDashboard extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function getTable(): string
    {
        /** @var string */
        return config('dashboard.database.tables.user_dashboards', parent::getTable());
    }

    protected function casts(): array
    {
        return [
            'layout' => 'array',
            'filters' => 'array',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Dashboard, $this> */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /** @return BelongsTo<DashboardPreset, $this> */
    public function activePreset(): BelongsTo
    {
        return $this->belongsTo(DashboardPreset::class, 'active_preset_id');
    }
}
