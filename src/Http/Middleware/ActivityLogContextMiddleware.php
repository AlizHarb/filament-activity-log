<?php

namespace AlizHarb\ActivityLog\Http\Middleware;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isAutoContextTrackingEnabled()) {
            return $next($request);
        }

        // Add our tap to Spatie's activity logger tabs for this request
        config(['activitylog.activity_logger_taps' => array_unique(array_merge(
            config('activitylog.activity_logger_taps', []),
            [\AlizHarb\ActivityLog\Taps\SetActivityContextTap::class]
        ))]);

        return $next($request);
    }

    /**
     * Check if auto context tracking is enabled in the current panel or config.
     */
    protected function isAutoContextTrackingEnabled(): bool
    {
        try {
            return ActivityLogPlugin::get()->isAutoContextTrackingEnabled();
        } catch (\Throwable $e) {
            return config('filament-activity-log.auto_context.enabled', true);
        }
    }
}
