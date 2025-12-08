<?php

use AlizHarb\ActivityLog\Taps\SetActivityContextTap;
use Spatie\Activitylog\Models\Activity;

it('adds ip and user_agent to activity properties', function () {
    // Mock request
    $ip = '123.123.123.123';
    $userAgent = 'Test Browser/1.0';

    request()->server->set('REMOTE_ADDR', $ip);
    request()->headers->set('User-Agent', $userAgent);

    $tap = new SetActivityContextTap;
    $activity = new Activity;
    $activity->properties = collect();

    $tap($activity, 'created');

    expect($activity->properties->get('ip_address'))->toBe($ip);
    expect($activity->properties->get('user_agent'))->toBe($userAgent);
});

it('merges with existing properties', function () {
    $tap = new SetActivityContextTap;
    $activity = new Activity;
    $activity->properties = collect(['existing_key' => 'existing_value']);

    $tap($activity, 'created');

    expect($activity->properties->has('existing_key'))->toBeTrue();
    expect($activity->properties->get('existing_key'))->toBe('existing_value');
    expect($activity->properties->has('ip_address'))->toBeTrue();
    expect($activity->properties->has('user_agent'))->toBeTrue();
});
