<?php

use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use Filament\Facades\Filament;

it('can register activity log resource', function () {
    $resources = Filament::getResources();

    expect($resources)->toContain(ActivityLogResource::class);
});

use AlizHarb\ActivityLog\Tests\Fixtures\User;

it('can render activity log resource page', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($user);

    expect($user->can('viewAny', \Spatie\Activitylog\Models\Activity::class))->toBeTrue();

    $this->get(ActivityLogResource::getUrl('index'))
        ->assertSuccessful();
});
