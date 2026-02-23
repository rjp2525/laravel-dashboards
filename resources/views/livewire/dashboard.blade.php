<div class="dashboard-grid">
    @foreach($widgets as $widget)
        <livewire:dashboard-widget
            :widget-key="$widget['key']"
            :dashboard-slug="$slug"
            :period="$period"
            :key="$widget['key']"
        />
    @endforeach
</div>
