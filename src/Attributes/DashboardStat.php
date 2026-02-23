<?php

namespace Reno\Dashboard\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DashboardStat
{
    /** @param array<int, string> $permissions */
    public function __construct(
        public readonly string $label,
        public readonly string $aggregate = 'count',
        public readonly ?string $column = null,
        public readonly ?string $key = null,
        public readonly ?string $dateColumn = null,
        public readonly ?string $icon = null,
        public readonly ?string $dashboard = null,
        public readonly ?string $scope = null,
        public readonly int $cacheTtl = 300,
        public readonly array $permissions = [],
    ) {}
}
