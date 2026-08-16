<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\CollectsActivityContext;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class RequestContextCollector implements CollectsActivityContext
{
    public function collect(Request $request, ?Model $subject = null, ?Model $causer = null): array
    {
        $context = [];

        if (config('filament-activity-log.auto_context.capture_ip', true) && ($ip = $request->ip())) {
            $context['ip_address'] = config('filament-activity-log.auto_context.anonymize_ip', false)
                ? IpUtils::anonymize($ip)
                : $ip;
        }

        if (config('filament-activity-log.auto_context.capture_browser', true)) {
            $context['user_agent'] = $request->userAgent();
        }

        if (config('filament-activity-log.auto_context.capture_request', true)) {
            $requestId = $request->header('X-Request-ID');
            $context['request_id'] = is_string($requestId) ? Str::substr($requestId, 0, 100) : null;
            $context['method'] = $request->method();
            $context['path'] = $request->path();
            $context['route'] = $request->route()?->getName();
        }

        if (config('filament-activity-log.auto_context.capture_tenant', true)) {
            $tenant = Filament::getTenant();

            if ($tenant) {
                $context['tenant_type'] = $tenant->getMorphClass();
                $context['tenant_id'] = $tenant->getKey();
            }
        }

        return array_filter($context, fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
