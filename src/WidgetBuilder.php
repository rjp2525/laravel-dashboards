<?php

namespace Reno\Dashboard;

use Closure;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\DataProviders\CallbackDataProvider;
use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Support\GridPosition;

class WidgetBuilder
{
    protected string $label = '';

    protected WidgetType $type = WidgetType::STAT;

    protected ?string $icon = null;

    protected ?string $description = null;

    protected ?string $component = null;

    protected ?GridPosition $position = null;

    protected ?DataProvider $provider = null;

    protected RefreshStrategy $refreshStrategy = RefreshStrategy::POLL;

    protected int $refreshInterval = 60;

    protected int $cacheTtl = 300;

    /** @var array<int, string> */
    protected array $permissions = [];

    public function __construct(
        protected DashboardManager $manager,
        protected string $key,
    ) {}

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function type(WidgetType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function component(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    public function position(int $x = 0, int $y = 0, int $w = 4, int $h = 2): static
    {
        $this->position = new GridPosition($x, $y, $w, $h);

        return $this;
    }

    public function provider(DataProvider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function using(Closure $callback): static
    {
        $this->provider = CallbackDataProvider::from($callback);

        return $this;
    }

    public function refresh(RefreshStrategy $strategy, int $interval = 60): static
    {
        $this->refreshStrategy = $strategy;
        $this->refreshInterval = $interval;

        return $this;
    }

    public function pollEvery(int $seconds): static
    {
        return $this->refresh(RefreshStrategy::POLL, $seconds);
    }

    public function cache(int $ttl): static
    {
        $this->cacheTtl = $ttl;

        return $this;
    }

    /** @param array<int, string> $permissions */
    public function permissions(array $permissions): static
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function register(): void
    {
        $widget = new InlineWidget(
            key: $this->key,
            label: $this->label,
            type: $this->type,
            icon: $this->icon,
            description: $this->description,
            componentName: $this->component,
            position: $this->position ?? new GridPosition,
            provider: $this->provider ?? CallbackDataProvider::from(fn (): null => null),
            refreshStrategyEnum: $this->refreshStrategy,
            refreshIntervalSeconds: $this->refreshInterval,
            cacheTtlSeconds: $this->cacheTtl,
            requiredPermissions: $this->permissions,
        );

        $this->manager->registerInline($this->key, $widget);
    }
}
