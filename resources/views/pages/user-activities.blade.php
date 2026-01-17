<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Page Description --}}
        <div class="activity-log-card">
            <div class="activity-log-body">
                <div class="flex items-start gap-x-3">
                    <div class="activity-log-icon-wrapper">
                        <x-filament::icon
                            icon="heroicon-o-information-circle"
                            class="activity-log-icon-lg activity-log-text-gray"
                        />
                    </div>
                    <div class="flex-1">
                        <h3 class="activity-log-user">
                            {{ __('filament-activity-log::activity.pages.user_activities.description_title') }}
                        </h3>
                        <div class="activity-log-description">
                            {{ __('filament-activity-log::activity.pages.user_activities.description') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activities Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
