<?php

namespace Reno\Dashboard\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WidgetDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $dashboardSlug,
        public string $widgetKey,
        /** @var array<string, mixed> */
        public array $data,
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
        return 'widget.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'widget_key' => $this->widgetKey,
            'data' => $this->data,
        ];
    }
}
