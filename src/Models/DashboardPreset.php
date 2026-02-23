<?php

namespace Reno\Dashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $dashboard_id
 * @property string $name
 * @property array<int, array<string, mixed>> $layout
 * @property bool $is_system
 * @property string|null $created_by_type
 * @property string|int|null $created_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dashboard $dashboard
 */
class DashboardPreset extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function getTable(): string
    {
        /** @var string */
        return config('dashboard.database.tables.dashboard_presets', parent::getTable());
    }

    protected function casts(): array
    {
        return [
            'layout' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /** @return BelongsTo<Dashboard, $this> */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /** @return MorphTo<Model, $this> */
    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }

    /** @param Builder<static> $query
     * @return Builder<static> */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /** @param Builder<static> $query
     * @return Builder<static> */
    public function scopeUser(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }
}
