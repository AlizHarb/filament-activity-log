<?php

use AlizHarb\ActivityLog\Support\ActivityLogRedactor;

it('redacts configured sensitive fields recursively', function () {
    config()->set('filament-activity-log.privacy.redaction.enabled', true);

    $redacted = ActivityLogRedactor::redact([
        'name' => 'Ali',
        'password' => 'secret-password',
        'meta' => [
            'api_token' => 'token-value',
            'safe' => 'visible',
        ],
    ]);

    expect($redacted)->toBe([
        'name' => 'Ali',
        'password' => '[redacted]',
        'meta' => [
            'api_token' => '[redacted]',
            'safe' => 'visible',
        ],
    ]);
});

it('can be disabled by configuration', function () {
    config()->set('filament-activity-log.privacy.redaction.enabled', false);

    expect(ActivityLogRedactor::redact(['password' => 'secret']))->toBe(['password' => 'secret']);
});
