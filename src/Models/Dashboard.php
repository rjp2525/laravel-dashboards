<?php

namespace Reno\Dashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<string, mixed>|null $grid_config
 * @property bool $is_default
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, DashboardWidget> $widgets
 * @property-read Collection<int, DashboardPreset> $presets
 * @property-read Collection<int, UserDashboard> $userDashboards
 */
class Dashboard extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function getTable(): string
    {
        /** @var string */
        return config('dashboard.database.tables.dashboards', parent::getTable());
    }

    protected function casts(): array
    {
        return [
            'grid_config' => 'array',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<DashboardWidget, $this> */
    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class)->orderBy('sort_order');
    }

    /** @return HasMany<DashboardPreset, $this> */
    public function presets(): HasMany
    {
        return $this->hasMany(DashboardPreset::class);
    }

    /** @return HasMany<UserDashboard, $this> */
    public function userDashboards(): HasMany
    {
        return $this->hasMany(UserDashboard::class);
    }

    /** @param Builder<static> $query
     * @return Builder<static> */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /** @param Builder<static> $query
     * @return Builder<static> */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
