<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('publishes the audit upgrade after the upstream activity table migration', function () {
    expect('upgrade_activity_log_with_audit_control_columns')
        ->toBeGreaterThan('create_activity_log_table');
});

it('can roll back safely after a partially applied migration', function () {
    $migration = include dirname(__DIR__, 2).'/database/migrations/upgrade_activity_log_with_audit_control_columns.php';

    Schema::table('activity_log', function (Blueprint $table): void {
        $table->dropIndex('activity_log_log_created_index');
    });

    try {
        $migration->down();

        expect(Schema::hasColumn('activity_log', 'risk_score'))->toBeFalse();
    } finally {
        $migration->up();
    }

    expect(Schema::hasColumn('activity_log', 'risk_score'))->toBeTrue()
        ->and(Schema::hasIndex('activity_log', 'activity_log_log_created_index'))->toBeTrue();
});
