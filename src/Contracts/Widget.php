<?php

namespace Reno\Dashboard\Contracts;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

interface Widget
{
    public function key(): string;

    public function label(): string;

    public function type(): WidgetType;

    public function icon(): ?string;

    public function description(): ?string;

    public function component(): string;

    public function defaultPosition(): GridPosition;

    public function dataProvider(): DataProvider;

    /** @return array<string, mixed> */
    public function toArray(): array;
}
