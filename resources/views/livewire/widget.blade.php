<div
    @if($this->refreshInterval)
        wire:poll.{{ $this->refreshInterval }}="loadData"
    @endif
>
    @if(empty($widgetData))
        <div class="dashboard-widget-loading">Loading...</div>
    @else
        <div class="dashboard-widget" data-widget-key="{{ $widgetKey }}">
            @if(!empty($widgetDefinition['label']))
                <h3 class="dashboard-widget-title">{{ $widgetDefinition['label'] }}</h3>
            @endif

            <div class="dashboard-widget-content">
                {{ $slot ?? '' }}
            </div>
        </div>
    @endif
</div>
