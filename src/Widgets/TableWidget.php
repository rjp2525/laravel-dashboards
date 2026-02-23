<?php

namespace Reno\Dashboard\Widgets;

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

abstract class TableWidget extends AbstractWidget
{
    protected int $perPage = 10;

    public function type(): WidgetType
    {
        return WidgetType::TABLE;
    }

    public function defaultPosition(): GridPosition
    {
        return new GridPosition(w: 6, h: 4, minW: 4, minH: 3);
    }

    public function component(): string
    {
        return 'TableWidget';
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'per_page' => $this->perPage(),
        ]);
    }
}
