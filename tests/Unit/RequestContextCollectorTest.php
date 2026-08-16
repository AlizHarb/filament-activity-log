<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\RequestContextCollector;
use Illuminate\Http\Request;

it('collects privacy-aware request context without query string values', function () {
    config()->set('filament-activity-log.auto_context.anonymize_ip', true);

    $request = Request::create('/admin/users?token=must-not-leak', 'POST', server: [
        'REMOTE_ADDR' => '192.168.10.42',
        'HTTP_USER_AGENT' => 'Audit Browser',
        'HTTP_X_REQUEST_ID' => 'request-123',
    ]);

    $context = app(RequestContextCollector::class)->collect($request);

    expect($context)->toMatchArray([
        'ip_address' => '192.168.10.0',
        'user_agent' => 'Audit Browser',
        'request_id' => 'request-123',
        'method' => 'POST',
        'path' => 'admin/users',
    ])->not->toContain('must-not-leak');
});
