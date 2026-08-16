<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Exceptions\ActivityMutationException;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;

it('provides a localized message and structured logging context', function () {
    $exception = new InvalidConfigurationException('configuration.scope_not_found', [
        'scope' => 'TenantScope',
    ]);

    expect($exception->getMessage())->toContain('TenantScope')
        ->and($exception->translationKey)->toBe('configuration.scope_not_found')
        ->and($exception->context())->toBe([
            'activity_log_error' => 'configuration.scope_not_found',
            'activity_log_error_context' => ['scope' => 'TenantScope'],
        ]);
});

it('uses domain-specific exception types for safe activity mutations', function () {
    $exception = new ActivityMutationException('mutation.subject_missing');

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe(
            __('filament-activity-log::activity.exceptions.mutation.subject_missing'),
        );
});
