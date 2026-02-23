<?php

namespace Reno\Dashboard\Attributes;

use Attribute;
use Reno\Dashboard\Enums\WidgetType;

#[Attribute(Attribute::TARGET_METHOD)]
class AsWidget
{
    /** @param array<int, string> $permissions */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly WidgetType $type = WidgetType::STAT,
        public readonly ?string $icon = null,
        public readonly ?string $description = null,
        public readonly ?string $dashboard = null,
        public readonly int $cacheTtl = 300,
        public readonly array $permissions = [],
    ) {}
}
