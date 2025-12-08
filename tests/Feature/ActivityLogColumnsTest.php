<?php

use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ListActivityLogs;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ViewActivityLog;
use AlizHarb\ActivityLog\Tests\Fixtures\User;

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
