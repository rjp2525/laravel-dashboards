<?php

namespace Reno\Dashboard\Widgets\Traits;

use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Support\RefreshConfig;

trait HasRefreshInterval
{
    protected RefreshStrategy $refreshStrategyEnum = RefreshStrategy::POLL;

    protected int $refreshIntervalSeconds = 60;

    public function refreshStrategy(): RefreshConfig
    {
        return new RefreshConfig($this->refreshStrategyEnum, $this->refreshIntervalSeconds);
    }

    public function refreshInterval(): ?int
    {
        return $this->refreshIntervalSeconds;
    }

    public function refreshUsing(RefreshStrategy $strategy, int $interval = 60): static
    {
        $this->refreshStrategyEnum = $strategy;
        $this->refreshIntervalSeconds = $interval;

        return $this;
    }

    public function pollEvery(int $seconds): static
    {
        return $this->refreshUsing(RefreshStrategy::POLL, $seconds);
    }

    public function pushUpdates(): static
    {
        return $this->refreshUsing(RefreshStrategy::PUSH, 0);
    }

    public function inertiaPolling(int $seconds = 60): static
    {
        return $this->refreshUsing(RefreshStrategy::INERTIA, $seconds);
    }

    public function manualRefresh(): static
    {
        return $this->refreshUsing(RefreshStrategy::MANUAL, 0);
    }
}
