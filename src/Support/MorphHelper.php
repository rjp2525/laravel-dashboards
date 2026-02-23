<?php

namespace Reno\Dashboard\Support;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Schema\Blueprint;

class MorphHelper
{
    /**
     * Add the appropriate morph columns based on the user model's key type.
     */
    public static function addMorphColumns(Blueprint $table, string $name, bool $nullable = false): void
    {
        $method = match (static::detectMorphType()) {
            'ulid' => $nullable ? 'nullableUlidMorphs' : 'ulidMorphs',
            'uuid' => $nullable ? 'nullableUuidMorphs' : 'uuidMorphs',
            default => $nullable ? 'nullableMorphs' : 'morphs',
        };

        $table->{$method}($name);
    }

    /**
     * Detect the morph type from the configured user model.
     *
     * @return string One of 'ulid', 'uuid', or 'id'.
     */
    public static function detectMorphType(): string
    {
        /** @var string $userModel */
        $userModel = config('dashboard.database.user_model', 'App\\Models\\User');

        if (! class_exists($userModel)) {
            return 'id';
        }

        $traits = class_uses_recursive($userModel);

        if (in_array(HasUlids::class, $traits, true)) {
            return 'ulid';
        }

        if (in_array(HasUuids::class, $traits, true)) {
            return 'uuid';
        }

        return 'id';
    }
}
