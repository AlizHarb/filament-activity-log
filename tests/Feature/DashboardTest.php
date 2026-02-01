<?php

use AlizHarb\ActivityLog\Pages\AuditDashboard;
use AlizHarb\ActivityLog\Tests\Fixtures\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('can render audit dashboard', function () {
    actingAs($this->user);

    get(AuditDashboard::getUrl())
        ->assertSuccessful()
        ->assertSee(__('filament-activity-log::activity.pages.audit_dashboard.title'));
});

it('can render dashboard widgets', function () {
    actingAs($this->user);

    get(AuditDashboard::getUrl())
        ->assertSuccessful();
});
