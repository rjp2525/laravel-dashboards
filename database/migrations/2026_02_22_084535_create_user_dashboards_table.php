<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Reno\Dashboard\Support\MorphHelper;

return new class extends Migration
{
    public function up(): void
    {
        $dashboardsTable = config('dashboard.database.tables.dashboards', 'dashboards');
        $presetsTable = config('dashboard.database.tables.dashboard_presets', 'dashboard_presets');

        Schema::create(config('dashboard.database.tables.user_dashboards', 'user_dashboards'), function (Blueprint $table) use ($dashboardsTable, $presetsTable): void {
            $table->ulid('id')->primary();
            MorphHelper::addMorphColumns($table, 'user');
            $table->foreignUlid('dashboard_id')
                ->constrained($dashboardsTable)
                ->cascadeOnDelete();
            $table->json('layout')->nullable();
            $table->foreignUlid('active_preset_id')
                ->nullable()
                ->constrained($presetsTable)
                ->nullOnDelete();
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'dashboard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('dashboard.database.tables.user_dashboards', 'user_dashboards'));
    }
};
