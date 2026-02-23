<?php

namespace Reno\Dashboard\Support;

use Reno\Dashboard\Enums\RefreshStrategy;

class RefreshConfig
{
    public function __construct(
        public readonly RefreshStrategy $strategy = RefreshStrategy::POLL,
        public readonly int $interval = 60,
    ) {}

    public static function poll(int $interval = 60): self
    {
        return new self(RefreshStrategy::POLL, $interval);
    }

    public static function push(): self
    {
        return new self(RefreshStrategy::PUSH, 0);
    }

    public static function inertia(int $interval = 60): self
    {
        return new self(RefreshStrategy::INERTIA, $interval);
    }

    public static function manual(): self
    {
        return new self(RefreshStrategy::MANUAL, 0);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'strategy' => $this->strategy->value,
            'interval' => $this->interval,
        ];
    }
}
