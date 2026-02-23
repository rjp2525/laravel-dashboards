<?php

namespace Reno\Dashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Enums\WidgetType;

/**
 * @property string $id
 * @property string $dashboard_id
 * @property string $widget_class
 * @property string $widget_key
 * @property string $label
 * @property WidgetType $type
 * @property array<string, mixed>|null $config
 * @property array<string, mixed>|null $default_position
 * @property RefreshStrategy $refresh_strategy
 * @property int|null $refresh_interval
 * @property int|null $cache_ttl
 * @property array<string, mixed>|null $permissions
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dashboard $dashboard
 */
class DashboardWidget extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function getTable(): string
    {
        /** @var string */
        return config('dashboard.database.tables.dashboard_widgets', parent::getTable());
    }

    protected function casts(): array
    {
        return [
            'type' => WidgetType::class,
            'config' => 'array',
            'default_position' => 'array',
            'refresh_strategy' => RefreshStrategy::class,
            'refresh_interval' => 'integer',
            'cache_ttl' => 'integer',
            'permissions' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Dashboard, $this> */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /** @param Builder<static> $query
     * @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
