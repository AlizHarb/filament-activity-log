<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\ActivityMutation;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->actor = User::create([
        'name' => 'Security Admin',
        'email' => 'security@example.com',
        'password' => bcrypt('password'),
    ]);

    actingAs($this->actor);
    config()->set('filament-activity-log.mutations.enabled', true);
    config()->set('filament-activity-log.mutations.authorize_subject', false);
    config()->set('filament-activity-log.mutations.log_compensating_activity', false);
    config()->set('filament-activity-log.mutations.custom_authorization', fn (): bool => true);
});

it('reverts only selected safe attributes', function () {
    $subject = User::create([
        'name' => 'New Name',
        'email' => 'new@example.com',
        'password' => 'new-secret',
    ]);

    $activity = Activity::create([
        'event' => 'updated',
        'description' => 'updated',
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->getKey(),
        'properties' => [
            'old' => ['name' => 'Old Name', 'email' => 'old@example.com', 'password' => 'old-secret'],
            'attributes' => ['name' => 'New Name', 'email' => 'new@example.com', 'password' => 'new-secret'],
        ],
    ]);

    $mutation = app(ActivityMutation::class);

    expect($mutation->previewRevert($activity))->toHaveKeys(['name', 'email'])
        ->not->toHaveKey('password');

    $mutation->revert($activity, ['name']);

    expect($subject->fresh()->name)->toBe('Old Name')
        ->and($subject->fresh()->email)->toBe('new@example.com')
        ->and($subject->fresh()->password)->toBe('new-secret');
});

it('blocks a revert that would overwrite a newer value', function () {
    $subject = User::create([
        'name' => 'Changed Again',
        'email' => 'conflict@example.com',
        'password' => 'secret',
    ]);

    $activity = Activity::create([
        'event' => 'updated',
        'description' => 'updated',
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->getKey(),
        'properties' => [
            'old' => ['name' => 'Old Name'],
            'attributes' => ['name' => 'New Name'],
        ],
    ]);

    expect(fn () => app(ActivityMutation::class)->revert($activity, ['name']))
        ->toThrow(RuntimeException::class);
});

it('requires a custom restorer when safe attributes cannot satisfy the model', function () {
    $activity = Activity::create([
        'event' => 'deleted',
        'description' => 'deleted',
        'subject_type' => User::class,
        'subject_id' => 999,
        'properties' => [
            'old' => [
                'id' => 999,
                'name' => 'Restored User',
                'email' => 'restored@example.com',
                'password' => 'leaked-secret',
                'created_at' => now()->subYear()->toDateTimeString(),
            ],
        ],
    ]);

    expect(fn () => app(ActivityMutation::class)->restore($activity))
        ->toThrow(RuntimeException::class, 'Configure a custom subject restorer');

    expect(User::query()->where('email', 'restored@example.com')->exists())->toBeFalse();
});

it('restores an explicitly permitted complete snapshot without system attributes', function () {
    config()->set('filament-activity-log.mutations.allow_sensitive_attributes', true);

    $activity = Activity::create([
        'event' => 'deleted',
        'description' => 'deleted',
        'subject_type' => User::class,
        'subject_id' => 999,
        'properties' => [
            'old' => [
                'id' => 999,
                'name' => 'Restored User',
                'email' => 'restored@example.com',
                'password' => 'preserved-hash',
                'created_at' => now()->subYear()->toDateTimeString(),
            ],
        ],
    ]);

    $restored = app(ActivityMutation::class)->restore($activity);

    expect($restored)->toBeInstanceOf(User::class)
        ->and($restored->getKey())->not->toBe(999)
        ->and($restored->email)->toBe('restored@example.com')
        ->and($restored->password)->toBe('preserved-hash')
        ->and($restored->created_at->isToday())->toBeTrue();
});

it('requires subject authorization by default', function () {
    config()->set('filament-activity-log.mutations.authorize_subject', true);

    $subject = User::create([
        'name' => 'New Name',
        'email' => 'denied@example.com',
        'password' => 'secret',
    ]);

    $activity = Activity::create([
        'event' => 'updated',
        'description' => 'updated',
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->getKey(),
        'properties' => [
            'old' => ['name' => 'Old Name'],
            'attributes' => ['name' => 'New Name'],
        ],
    ]);

    expect(fn () => app(ActivityMutation::class)->revert($activity, ['name']))
        ->toThrow(AuthorizationException::class);
});

it('enforces activity authorization when the mutation service is called directly', function () {
    config()->set('filament-activity-log.mutations.custom_authorization', fn (): bool => false);

    $subject = User::create([
        'name' => 'New Name',
        'email' => 'activity-denied@example.com',
        'password' => 'secret',
    ]);

    $activity = Activity::create([
        'event' => 'updated',
        'description' => 'updated',
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->getKey(),
        'properties' => [
            'old' => ['name' => 'Old Name'],
            'attributes' => ['name' => 'New Name'],
        ],
    ]);

    expect(fn () => app(ActivityMutation::class)->revert($activity, ['name']))
        ->toThrow(AuthorizationException::class)
        ->and($subject->fresh()->name)->toBe('New Name');
});
