<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\ActivityPruner;
use AlizHarb\ActivityLog\Support\RetentionManager;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

it('prunes only authorized, scoped, expired activities without retention holds', function () {
    $actor = User::create([
        'name' => 'Compliance Admin',
        'email' => 'pruner@example.com',
        'password' => bcrypt('password'),
    ]);

    actingAs($actor);
    config()->set('filament-activity-log.mutations.enabled', true);
    config()->set(
        'filament-activity-log.mutations.custom_authorization',
        fn (string $ability): bool => in_array($ability, ['hold', 'prune'], true)
    );
    config()->set('filament-activity-log.retention.log_activity', false);

    $expired = Activity::create(['description' => 'Expired', 'created_at' => now()->subDays(40)]);
    $held = Activity::create(['description' => 'Held', 'created_at' => now()->subDays(40)]);
    $current = Activity::create(['description' => 'Current', 'created_at' => now()]);

    app(RetentionManager::class)->setHold($held, true);

    expect(app(ActivityPruner::class)->pruneBefore(now()->subDays(30)))->toBe(1)
        ->and(Activity::query()->find($expired->getKey()))->toBeNull()
        ->and(Activity::query()->find($held->getKey()))->not->toBeNull()
        ->and(Activity::query()->find($current->getKey()))->not->toBeNull();
});
