<div class="activity-log-timeline" x-data>
    @php
        $activities = $activities ?? $getState() ?? collect();
        // Ensure it's a collection to avoid errors
        if (!$activities instanceof \Illuminate\Support\Collection) {
            $activities = collect($activities);
        }
    @endphp

    @forelse ($activities as $key => $activity)
        <div class="activity-log-item group">
            {{-- Connecting Line --}}
            @if (!$loop->last)
                <div class="activity-log-line"></div>
            @endif

            {{-- Icon / Avatar --}}
            <div class="activity-log-icon-wrapper">
                @php
                    $config = config('filament-activity-log.events.' . $activity->event, [
                        'icon' => 'heroicon-m-information-circle',
                        'color' => 'gray',
                    ]);
                    $icon = $config['icon'];
                    // We still use Tailwind text colors for icons as they are dynamic
                    $color = match ($config['color']) {
                        'success' => 'activity-log-text-success',
                        'warning' => 'activity-log-text-warning',
                        'danger' => 'activity-log-text-danger',
                        'info' => 'activity-log-text-info',
                        default => 'activity-log-text-gray',
                    };
                @endphp

                @if($activity->causer && method_exists($activity->causer, 'getFilamentAvatarUrl'))
                    <img src="{{ $activity->causer->getFilamentAvatarUrl() }}" alt="{{ $activity->causer->name }}"
                        class="activity-log-avatar" />
                    <div class="activity-log-avatar-icon-wrapper">
                        <x-filament::icon :icon="$icon" class="activity-log-icon-xs {{ $color }}" />
                    </div>
                @else
                    <x-filament::icon :icon="$icon" class="activity-log-icon-lg {{ $color }}" />
                @endif
            </div>

            {{-- Content Card --}}
            <div class="activity-log-card">
                {{-- Header --}}
                <div class="activity-log-header">
                    <div class="activity-log-header-content">
                        <span class="activity-log-user">
                            {{ $activity->causer?->name ?? 'System' }}
                        </span>
                        <span class="activity-log-event">
                            {{ ucfirst($activity->event) }}
                        </span>
                        <span class="activity-log-meta">
                            {{ class_basename($activity->subject_type) }}
                            @if($activity->subject_id)
                                <span class="activity-log-subject-id">#{{ $activity->subject_id }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="activity-log-meta-wrapper">
                        <time datetime="{{ $activity->created_at->toIso8601String() }}" class="activity-log-time"
                            title="{{ $activity->created_at->format(config('filament-activity-log.datetime_format', 'M d, Y H:i:s')) }}">
                            <x-heroicon-m-calendar class="activity-log-icon-sm activity-log-icon-opacity-70" />
                            {{ $activity->created_at->diffForHumans() }}
                        </time>
                    </div>
                </div>

                {{-- Body --}}
                <div class="activity-log-body">
                    @if($activity->description)
                        <div class="activity-log-description">
                            {{ $activity->description }}
                        </div>
                    @endif

                    {{-- Metadata (IP, UA) --}}
                    @if(isset($activity->properties['ip_address']) || isset($activity->properties['user_agent']))
                        <div class="activity-log-footer">
                            @if(isset($activity->properties['ip_address']))
                                <div class="activity-log-badge">
                                    <x-heroicon-m-globe-alt class="activity-log-icon-sm" />
                                    {{ $activity->properties['ip_address'] }}
                                </div>
                            @endif
                            @if(isset($activity->properties['user_agent']))
                                <div class="activity-log-badge activity-log-badge-truncate"
                                    title="{{ $activity->properties['user_agent'] }}">
                                    <x-heroicon-m-device-phone-mobile class="activity-log-icon-sm" />
                                    {{ $activity->properties['user_agent'] }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Changes Toggle --}}
                    @if($activity->properties->has('attributes') || $activity->properties->has('old'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="activity-log-changes-btn">
                                <span class="activity-log-changes-btn-content">
                                    <x-heroicon-m-arrows-right-left class="activity-log-icon-md" />
                                    {{ __('filament-activity-log::activity.infolist.tab.changes') }}
                                </span>
                                <x-heroicon-m-chevron-down class="activity-log-icon-md activity-log-toggle-icon"
                                    x-bind:class="{ 'activity-log-rotate-180': open }" />
                            </button>

                            <div x-show="open" x-collapse class="activity-log-changes-grid" style="display: none;">
                                @if($activity->properties->has('old'))
                                    <div class="activity-log-change-card old">
                                        <div class="activity-log-change-header">
                                            {{ __('filament-activity-log::activity.infolist.tab.old') }}
                                        </div>
                                        <div class="activity-log-change-body">
                                            @if(is_array($activity->properties['old']))
                                                @foreach($activity->properties['old'] as $key => $value)
                                                    <div class="activity-log-change-item">
                                                        <dt class="activity-log-change-key">{{ str($key)->title() }}</dt>
                                                        <dd class="activity-log-change-value">
                                                            {{ is_array($value) ? json_encode($value) : $value }}
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="activity-log-simple-value">
                                                    {{ $activity->properties['old'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($activity->properties->has('attributes'))
                                    <div class="activity-log-change-card new">
                                        <div class="activity-log-change-header">
                                            {{ __('filament-activity-log::activity.infolist.tab.new') }}
                                        </div>
                                        <div class="activity-log-change-body">
                                            @if(is_array($activity->properties['attributes']))
                                                @foreach($activity->properties['attributes'] as $key => $value)
                                                    <div class="activity-log-change-item">
                                                        <dt class="activity-log-change-key">{{ str($key)->title() }}</dt>
                                                        <dd class="activity-log-change-value">
                                                            {{ is_array($value) ? json_encode($value) : $value }}
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="activity-log-simple-value">
                                                    {{ $activity->properties['attributes'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="activity-log-empty-state">
            <div class="activity-log-empty-icon">
                <x-heroicon-o-clipboard-document-list class="activity-log-icon-lg" style="width: 1.5rem; height: 1.5rem;" />
            </div>
            <h3 class="activity-log-empty-title">
                {{ __('filament-activity-log::activity.action.timeline.empty_state_title') }}
            </h3>
            <p class="activity-log-empty-description">
                {{ __('filament-activity-log::activity.action.timeline.empty_state_description') }}
            </p>
        </div>
    @endforelse
</div>