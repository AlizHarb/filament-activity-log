<?php

use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ListActivityLogs;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ViewActivityLog;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Livewire\livewire;

it('renders ip and browser columns in table', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    livewire(ListActivityLogs::class)
        ->assertTableColumnVisible('properties.ip_address')
        ->assertTableColumnVisible('properties.user_agent');
});

it('renders subject id column in table', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    livewire(ListActivityLogs::class)
        ->assertTableColumnVisible('subject_id');
});

it('can filter activity logs by subject id', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $matchingActivity = Activity::query()->create([
        'log_name' => 'default',
        'description' => 'Matched subject',
        'subject_type' => User::class,
        'subject_id' => 101,
        'event' => 'updated',
        'causer_type' => User::class,
        'causer_id' => $user->getKey(),
        'properties' => [],
    ]);

    $otherActivity = Activity::query()->create([
        'log_name' => 'default',
        'description' => 'Other subject',
        'subject_type' => User::class,
        'subject_id' => 202,
        'event' => 'updated',
        'causer_type' => User::class,
        'causer_id' => $user->getKey(),
        'properties' => [],
    ]);

    $this->actingAs($user);

    livewire(ListActivityLogs::class)
        ->filterTable('subject_id', ['value' => 101])
        ->assertCanSeeTableRecords([$matchingActivity])
        ->assertCanNotSeeTableRecords([$otherActivity]);
});

it('renders ip and browser in infolist', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $activity = activity()->log('test');

    // Manually add properties for the test
    $activity->properties = $activity->properties->merge([
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
    ]);
    $activity->save();

    $this->actingAs($user);

    livewire(ViewActivityLog::class, ['record' => $activity->getKey()])
        ->assertSee('127.0.0.1')
        ->assertSee('Mozilla/5.0');
});
