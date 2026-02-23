<?php

namespace Reno\Dashboard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Reno\Dashboard\Events\WidgetDataUpdated;

class BroadcastWidgetUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $dashboardSlug,
        public string $widgetKey,
        /** @var array<string, mixed> */
        public array $data,
    ) {}

    public function handle(): void
    {
        WidgetDataUpdated::dispatch(
            $this->dashboardSlug,
            $this->widgetKey,
            $this->data,
        );
    }
}
