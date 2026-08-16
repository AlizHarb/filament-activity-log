<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Commands;

use AlizHarb\ActivityLog\Support\ActivityIntegrity;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\AuditSchema;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class VerifyIntegrityCommand extends Command
{
    protected $signature = 'filament-activity-log:verify-integrity {--chunk=500 : Records verified per chunk}';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription((string) __('filament-activity-log::activity.commands.verify_integrity.description'));
    }

    public function handle(ActivityQuery $activities, AuditSchema $schema, ActivityIntegrity $integrity): int
    {
        if (! $schema->hasIntegrityHash()) {
            $this->components->error(__('filament-activity-log::activity.commands.verify_integrity.migration_required'));

            return self::FAILURE;
        }

        $checked = 0;
        $invalid = [];

        $activities->query()->chunkById(max(1, (int) $this->option('chunk')), function ($records) use ($integrity, &$checked, &$invalid): void {
            $records->each(function (Activity $activity) use ($integrity, &$checked, &$invalid): void {
                $checked++;

                if (! $integrity->verify($activity)) {
                    $invalid[] = $activity->getKey();
                }
            });
        });

        if ($invalid !== []) {
            $this->components->error(__('filament-activity-log::activity.commands.verify_integrity.failed', [
                'ids' => implode(', ', $invalid),
            ]));

            return self::FAILURE;
        }

        $this->components->info(__('filament-activity-log::activity.commands.verify_integrity.completed', ['count' => $checked]));

        return self::SUCCESS;
    }
}
