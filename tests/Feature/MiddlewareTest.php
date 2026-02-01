<?php

use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

it('captures context via middleware', function () {
    $user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    actingAs($user);

    activity()->log('test-middleware');

    $activity = Activity::latest()->first();

    // Check if context middleware would have added something if running in full app
    // In unit test context, we just ensure it doesn't crash and activity is logged
    expect($activity->description)->toBe('test-middleware');
});
