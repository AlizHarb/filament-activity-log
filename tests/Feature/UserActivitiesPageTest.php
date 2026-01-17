<?php

use AlizHarb\ActivityLog\Pages\UserActivitiesPage;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]));
});

it('can render user activities page', function () {
    livewire(UserActivitiesPage::class)
        ->assertSuccessful();
});

it('can list user activities', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Test Activity',
        'event' => 'created',
        'causer_id' => 1,
        'causer_type' => User::class,
    ]);

    livewire(UserActivitiesPage::class)
        ->assertCanSeeTableRecords([$activity])
        ->assertSee('Test Activity');
});

it('can filter by causer', function () {
    $activity1 = Activity::create([
        'log_name' => 'default',
        'description' => 'Activity 1',
        'causer_id' => 1,
        'causer_type' => User::class,
    ]);

    $activity2 = Activity::create([
        'log_name' => 'default',
        'description' => 'Activity 2',
        'causer_id' => 2,
        'causer_type' => User::class,
    ]);

    livewire(UserActivitiesPage::class)
        ->filterTable('causer_id', 1)
        ->assertCanSeeTableRecords([$activity1])
        ->assertCanNotSeeTableRecords([$activity2]);
});
