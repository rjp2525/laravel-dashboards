<?php

namespace Reno\Dashboard\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Dashboardable
{
    public function __construct(
        public readonly string $dateColumn = 'created_at',
        public readonly ?string $dashboard = null,
        public readonly ?string $scope = null,
    ) {}
}
