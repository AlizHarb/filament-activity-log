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
        ->assertTableColumnVisible('ip_address')
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

it('renders risk column in table', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    livewire(ListActivityLogs::class)
        ->assertTableColumnVisible('risk');
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
        ->loadTable()
        ->filterTable('subject_id', ['value' => 101])
        ->assertCanSeeTableRecords([$matchingActivity])
        ->assertCanNotSeeTableRecords([$otherActivity]);
});

it('can correlate activity logs by request id and ip address', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'correlation@example.com',
        'password' => bcrypt('password'),
    ]);

    $matchingActivity = Activity::query()->create([
        'log_name' => 'default',
        'description' => 'Matching request',
        'event' => 'updated',
        'properties' => ['request_id' => 'req-123', 'ip_address' => '192.0.2.10'],
    ]);
    $otherActivity = Activity::query()->create([
        'log_name' => 'default',
        'description' => 'Other request',
        'event' => 'updated',
        'properties' => ['request_id' => 'req-456', 'ip_address' => '192.0.2.11'],
    ]);

    $this->actingAs($user);

    livewire(ListActivityLogs::class)
        ->loadTable()
        ->filterTable('request_id', ['value' => 'req-123'])
        ->filterTable('ip_address', ['value' => '192.0.2.10'])
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

it('redacts sensitive values in the infolist', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $activity = Activity::query()->create([
        'log_name' => 'default',
        'description' => 'Password changed',
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'event' => 'updated',
        'causer_type' => User::class,
        'causer_id' => $user->getKey(),
        'properties' => [
            'attributes' => ['password' => 'new-secret'],
            'old' => ['password' => 'old-secret'],
        ],
    ]);

    $this->actingAs($user);

    livewire(ViewActivityLog::class, ['record' => $activity->getKey()])
        ->assertSee('[redacted]')
        ->assertDontSee('new-secret')
        ->assertDontSee('old-secret');
});
