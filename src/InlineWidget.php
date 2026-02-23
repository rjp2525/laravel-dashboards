<?php

namespace Reno\Dashboard;

use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;
use Reno\Dashboard\Support\RefreshConfig;
use Reno\Dashboard\Widgets\AbstractWidget;

class InlineWidget extends AbstractWidget
{
    /** @param array<int, string> $requiredPermissions */
    public function __construct(
        protected string $key,
        protected string $label,
        protected WidgetType $type,
        protected ?string $icon,
        protected ?string $description,
        protected ?string $componentName,
        protected GridPosition $position,
        protected DataProvider $provider,
        protected RefreshStrategy $refreshStrategyEnum = RefreshStrategy::POLL,
        protected int $refreshIntervalSeconds = 60,
        protected int $cacheTtlSeconds = 300,
        protected array $requiredPermissions = [],
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): WidgetType
    {
        return $this->type;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function component(): string
    {
        return $this->componentName ?? parent::component();
    }

    public function defaultPosition(): GridPosition
    {
        return $this->position;
    }

    public function dataProvider(): DataProvider
    {
        return $this->provider;
    }

    public function cacheTtl(): int
    {
        return $this->cacheTtlSeconds;
    }

    public function refreshStrategy(): RefreshConfig
    {
        return new RefreshConfig($this->refreshStrategyEnum, $this->refreshIntervalSeconds);
    }

    public function refreshInterval(): ?int
    {
        return $this->refreshIntervalSeconds;
    }
}
