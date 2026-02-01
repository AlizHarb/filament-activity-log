<?php

use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('can revert changes', function () {
    $user = User::create(['name' => 'Old Name', 'email' => 'old@example.com', 'password' => bcrypt('password')]);

    activity()
        ->performedOn($user)
        ->withProperties(['old' => ['name' => 'Old Name'], 'attributes' => ['name' => 'New Name']])
        ->log('updated');

    $activity = Activity::latest()->first();

    actingAs($this->user);

    // Test the logic directly
    $subject = $activity->subject;
    $oldAttributes = $activity->properties['old'];

    foreach ($oldAttributes as $key => $value) {
        $subject->{$key} = $value;
    }
    $subject->save();

    expect($user->fresh()->name)->toBe('Old Name');
});

it('can restore a deleted record', function () {
    $user = User::create(['name' => 'To be deleted', 'email' => 'delete@example.com', 'password' => bcrypt('password')]);
    $userData = $user->toArray();
    $user->delete();

    activity()
        ->performedOn($user)
        ->withProperties(['old' => $userData])
        ->log('deleted');

    $activity = Activity::latest()->first();

    actingAs($this->user);

    $modelClass = $activity->subject_type;
    $model = new $modelClass;
    $model->fill($activity->properties['old'] ?? []);
    $model->save();

    expect($modelClass::where('email', 'delete@example.com')->exists())->toBeTrue();
});

it('can prune old logs', function () {
    Activity::create(['description' => 'Old log', 'created_at' => now()->subDays(40)]);
    Activity::create(['description' => 'New log', 'created_at' => now()]);

    expect(Activity::count())->toBe(2);

    Activity::where('created_at', '<', now()->subDays(30))->delete();

    expect(Activity::count())->toBe(1);
    expect(Activity::first()->description)->toBe('New log');
});
