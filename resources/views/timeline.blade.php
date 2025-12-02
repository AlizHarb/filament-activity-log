<div class="activity-log-timeline" x-data>
    @php
        $activities = $getState();
    @endphp

    @forelse ($activities as $key => $activity)
        <div class="activity-log-item group">
            {{-- Connecting Line --}}
            @if (! $loop->last)
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
                        'success' => 'text-success-500',
                        'warning' => 'text-warning-500',
                        'danger' => 'text-danger-500',
                        'info' => 'text-info-500',
                        default => 'text-gray-500',
                    };
                @endphp
                
                @if($activity->causer && method_exists($activity->causer, 'getFilamentAvatarUrl'))
                    <img src="{{ $activity->causer->getFilamentAvatarUrl() }}" alt="{{ $activity->causer->name }}" class="h-full w-full rounded-full object-cover" />
                    <div class="absolute -bottom-1 -right-1 rounded-full bg-white dark:bg-gray-900 p-0.5">
                        <x-filament::icon :icon="$icon" class="size-3 {{ $color }}" />
                    </div>
                @else
                    <x-filament::icon :icon="$icon" class="size-5 {{ $color }}" />
                @endif
            </div>

            {{-- Content Card --}}
            <div class="activity-log-card">
                {{-- Header --}}
                <div class="activity-log-header">
                    <div class="flex items-center gap-x-2 overflow-hidden">
                        <span class="activity-log-user">
                            {{ $activity->causer?->name ?? 'System' }}
                        </span>
                        <span class="activity-log-event">
                            {{ ucfirst($activity->event) }}
                        </span>
                        <span class="activity-log-meta">
                            {{ class_basename($activity->subject_type) }}
                            @if($activity->subject_id)
                                <span class="font-mono text-xs opacity-70">#{{ $activity->subject_id }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="activity-log-meta flex shrink-0 items-center gap-x-3">
                        <time datetime="{{ $activity->created_at->toIso8601String() }}" class="flex items-center gap-x-1" title="{{ $activity->created_at->format(config('filament-activity-log.datetime_format', 'M d, Y H:i:s')) }}">
                            <x-filament::icon icon="heroicon-m-calendar" class="size-3.5 opacity-70" />
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
                    @if(isset($activity->properties['ip']) || isset($activity->properties['user_agent']))
                        <div class="activity-log-footer">
                            @if(isset($activity->properties['ip']))
                                <div class="activity-log-badge">
                                    <x-filament::icon icon="heroicon-m-globe-alt" class="size-3.5" />
                                    {{ $activity->properties['ip'] }}
                                </div>
                            @endif
                            @if(isset($activity->properties['user_agent']))
                                <div class="activity-log-badge max-w-full sm:max-w-xs truncate" title="{{ $activity->properties['user_agent'] }}">
                                    <x-filament::icon icon="heroicon-m-device-phone-mobile" class="size-3.5" />
                                    {{ $activity->properties['user_agent'] }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Changes Toggle --}}
                    @if($activity->properties->has('attributes') || $activity->properties->has('old'))
                        <div x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                type="button" 
                                class="activity-log-changes-btn"
                            >
                                <span class="flex items-center gap-x-2">
                                    <x-filament::icon icon="heroicon-m-arrows-right-left" class="size-4" />
                                    {{ __('filament-activity-log::activity.infolist.tab.changes') }}
                                </span>
                                <x-filament::icon 
                                    icon="heroicon-m-chevron-down" 
                                    class="size-4 transition-transform duration-200" 
                                    x-bind:class="{ 'rotate-180': open }"
                                />
                            </button>

                            <div 
                                x-show="open" 
                                x-collapse 
                                class="activity-log-changes-grid"
                                style="display: none;"
                            >
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
                                                <div class="p-3 text-xs">
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
                                                <div class="p-3 text-xs">
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
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="activity-log-icon-wrapper" style="background: var(--c-bg-secondary); box-shadow: none;">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="size-6 text-gray-400" />
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No activity logs found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no activities recorded for this record yet.</p>
        </div>
    @endforelse
</div>