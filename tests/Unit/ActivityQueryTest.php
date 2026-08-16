<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Exporters\ActivityLogExporter;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Tests\Fixtures\CustomActivityModel;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

it('uses the configured activity model', function () {
    config()->set('activitylog.activity_model', CustomActivityModel::class);

    expect(app(ActivityQuery::class)->modelClass())->toBe(CustomActivityModel::class)
        ->and(ActivityLogExporter::getModel())->toBe(CustomActivityModel::class);
});

it('applies the configured security scope to activity queries and exports', function () {
    Activity::create(['log_name' => 'tenant-a', 'description' => 'Visible']);
    Activity::create(['log_name' => 'tenant-b', 'description' => 'Hidden']);

    config()->set(
        'filament-activity-log.query.scope',
        fn (Builder $query): Builder => $query->where('log_name', 'tenant-a')
    );

    $activityQuery = app(ActivityQuery::class);

    expect($activityQuery->query()->pluck('description')->all())->toBe(['Visible'])
        ->and(ActivityLogExporter::modifyQuery(Activity::query())->pluck('description')->all())->toBe(['Visible']);
});

it('builds a scoped timeline for subject and causer activity', function () {
    $user = User::create([
        'name' => 'Timeline User',
        'email' => 'timeline@example.com',
        'password' => bcrypt('password'),
    ]);

    Activity::create([
        'log_name' => 'tenant-a',
        'description' => 'Subject activity',
        'subject_type' => $user->getMorphClass(),
        'subject_id' => $user->getKey(),
    ]);
    Activity::create([
        'log_name' => 'tenant-a',
        'description' => 'Causer activity',
        'causer_type' => $user->getMorphClass(),
        'causer_id' => $user->getKey(),
    ]);
    Activity::create([
        'log_name' => 'tenant-b',
        'description' => 'Other tenant',
        'subject_type' => $user->getMorphClass(),
        'subject_id' => $user->getKey(),
    ]);

    config()->set(
        'filament-activity-log.query.scope',
        fn (Builder $query): Builder => $query->where('log_name', 'tenant-a')
    );

    expect(app(ActivityQuery::class)->forRecord($user)->pluck('description')->all())
        ->toEqualCanonicalizing(['Subject activity', 'Causer activity']);
});
