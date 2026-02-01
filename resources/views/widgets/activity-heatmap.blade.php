<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('filament-activity-log::activity.widgets.heatmap.heading') }}
        </x-slot>

        @php
            $heatmap = $this->getData();
            $data = $heatmap['data'];
            $max = $heatmap['max'];
            $startDate = now()->subDays(365)->startOfWeek();
            $endDate = now();
            
            // Calculate months with smart spacing to avoid overlap
            $months = [];
            $currentMonth = $startDate->format('M');
            $months[] = ['name' => $currentMonth, 'week_index' => 0];
            $lastLabelWeek = 0;
            
            $dt = $startDate->copy();
            for ($weekIndex = 0; $weekIndex < 52; $weekIndex++) {
                $month = $dt->addWeek()->format('M');
                if ($month !== $currentMonth) {
                    // Only add label if it's been at least 4 weeks since the last one
                    if (($weekIndex - $lastLabelWeek) >= 4) {
                        $months[] = ['name' => $month, 'week_index' => $weekIndex];
                        $lastLabelWeek = $weekIndex;
                        $currentMonth = $month;
                    }
                }
            }
            
            // Grid cell dimensions for label calculation
            $cellSize = 11; // px
            $gap = 3; // px
            $weekWidth = $cellSize + $gap;
        @endphp

        <div style="overflow-x: auto; padding: 1rem 0;">
            <!-- Month Labels -->
            <div style="position: relative; height: 16px; margin-bottom: 8px; font-size: 10px; font-weight: 600; color: #9ca3af;">
                @foreach ($months as $month)
                    <div style="position: absolute; left: {{ $month['week_index'] * $weekWidth }}px; white-space: nowrap;">
                        {{ $month['name'] }}
                    </div>
                @endforeach
            </div>

            <!-- Heatmap Grid -->
            <div style="
                display: grid; 
                grid-template-rows: repeat(7, {{ $cellSize }}px); 
                grid-auto-flow: column; 
                gap: {{ $gap }}px; 
                width: max-content;
            ">
                @foreach (range(0, 52) as $week)
                    @foreach (range(0, 6) as $day)
                        @php
                            $currentDate = $startDate->copy()->addWeeks($week)->addDays($day);
                            $dateString = $currentDate->toDateString();
                            $count = $data[$dateString] ?? 0;
                            $intensity = $count > 0 ? ceil(($count / $max) * 4) : 0;
                            
                            // Universal colors (subtler empty state)
                            $bg = match ($intensity) {
                                0 => 'rgba(128, 128, 128, 0.1)', // More subtle empty state
                                1 => '#22c55e40', 
                                2 => '#22c55e80', 
                                3 => '#22c55ebf', 
                                4 => '#22c55e',   
                                default => 'rgba(128, 128, 128, 0.1)',
                            };
                            
                            $tooltip = __('filament-activity-log::activity.widgets.heatmap.tooltip', [
                                'count' => $count,
                                'date' => $currentDate->format('M j, Y'),
                            ]);
                        @endphp
                        
                        @if ($currentDate <= $endDate)
                            <div 
                                class="filament-activity-log-heatmap-cell group"
                                style="
                                    width: {{ $cellSize }}px; 
                                    height: {{ $cellSize }}px; 
                                    border-radius: 2px; 
                                    background-color: {{ $bg }};
                                    transition: transform 0.15s ease;
                                    cursor: pointer;
                                "
                                x-tooltip="{ content: '{{ $tooltip }}' }"
                                onmouseover="this.style.transform='scale(1.4)'; this.style.zIndex='10';"
                                onmouseout="this.style.transform='scale(1)'; this.style.zIndex='1';"
                            ></div>
                        @else
                            <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px;"></div>
                        @endif
                    @endforeach
                @endforeach
            </div>
            
            <!-- Legend -->
            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; font-size: 0.75rem; color: #9ca3af;">
                <span>{{ __('filament-activity-log::activity.widgets.heatmap.less') }}</span>
                <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px; border-radius: 2px; background-color: rgba(128, 128, 128, 0.1);"></div>
                <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px; border-radius: 2px; background-color: #22c55e40;"></div>
                <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px; border-radius: 2px; background-color: #22c55e80;"></div>
                <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px; border-radius: 2px; background-color: #22c55ebf;"></div>
                <div style="width: {{ $cellSize }}px; height: {{ $cellSize }}px; border-radius: 2px; background-color: #22c55e;"></div>
                <span>{{ __('filament-activity-log::activity.widgets.heatmap.more') }}</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
