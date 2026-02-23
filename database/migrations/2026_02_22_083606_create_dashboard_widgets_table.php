<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dashboardsTable = config('dashboard.database.tables.dashboards', 'dashboards');

        Schema::create(config('dashboard.database.tables.dashboard_widgets', 'dashboard_widgets'), function (Blueprint $table) use ($dashboardsTable): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('dashboard_id')
                ->constrained($dashboardsTable)
                ->cascadeOnDelete();
            $table->string('widget_class');
            $table->string('widget_key')->unique();
            $table->string('label');
            $table->string('type');
            $table->json('config')->nullable();
            $table->json('default_position')->nullable();
            $table->string('refresh_strategy')->default('poll');
            $table->unsignedInteger('refresh_interval')->nullable();
            $table->unsignedInteger('cache_ttl')->nullable();
            $table->json('permissions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('dashboard.database.tables.dashboard_widgets', 'dashboard_widgets'));
    }
};
