<?php

namespace Reno\Dashboard\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresetApplied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $dashboardSlug,
        public string $presetId,
        public string $userId,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $prefixConfig = config('dashboard.broadcasting.channel_prefix', 'dashboard');
        $prefix = is_string($prefixConfig) ? $prefixConfig : 'dashboard';

        return [
            new Channel("{$prefix}.{$this->dashboardSlug}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'preset.applied';
    }
}
