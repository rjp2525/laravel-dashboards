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

        Schema::create(config('dashboard.database.tables.dashboard_presets', 'dashboard_presets'), function (Blueprint $table) use ($dashboardsTable): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('dashboard_id')
                ->constrained($dashboardsTable)
                ->cascadeOnDelete();
            $table->string('name');
            $table->json('layout');
            $table->boolean('is_system')->default(false);
            MorphHelper::addMorphColumns($table, 'created_by', nullable: true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('dashboard.database.tables.dashboard_presets', 'dashboard_presets'));
    }
};
