<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Commands;

use AlizHarb\ActivityLog\Support\ActivityCache;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\AuditMetadata;
use AlizHarb\ActivityLog\Support\AuditSchema;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class BackfillAuditMetadataCommand extends Command
{
    protected $signature = 'filament-activity-log:backfill
        {--chunk=500 : Records processed per chunk}
        {--from-id=0 : Start after this activity ID}
        {--only-missing : Process only records missing risk or integrity metadata}';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription((string) __('filament-activity-log::activity.commands.backfill.description'));
    }

    public function handle(ActivityQuery $activities, AuditSchema $schema, AuditMetadata $metadata, ActivityCache $cache): int
    {
        if (! $schema->hasRiskColumns()) {
            $this->components->error(__('filament-activity-log::activity.commands.backfill.migration_required'));

            return self::FAILURE;
        }

        $processed = 0;
        $chunk = max(1, (int) $this->option('chunk'));
        $query = $activities->query();
        $qualifiedKey = $query->getModel()->getQualifiedKeyName();
        $query->where($qualifiedKey, '>', max(0, (int) $this->option('from-id')));

        if ((bool) $this->option('only-missing')) {
            $missingColumns = [];

            if (config('filament-activity-log.risk.enabled', true)) {
                $missingColumns[] = 'risk_level';
            }

            if ($schema->hasIntegrityHash() && config('filament-activity-log.integrity.enabled', true)) {
                $missingColumns[] = 'integrity_hash';
            }

            if ($missingColumns === []) {
                $query->whereRaw('1 = 0');
            }

            $query->where(function ($query) use ($missingColumns): void {
                foreach ($missingColumns as $index => $column) {
                    if ($index === 0) {
                        $query->whereNull($column);

                        continue;
                    }

                    $query->orWhereNull($column);
                }
            });
        }

        $query
            ->orderBy($qualifiedKey)
            ->chunkById($chunk, function ($records) use ($metadata, &$processed): void {
                $records->each(function (Activity $activity) use ($metadata, &$processed): void {
                    $metadata->persist($activity, refresh: false);
                    $processed++;
                });
            });

        $cache->flush();

        $this->components->info(__('filament-activity-log::activity.commands.backfill.completed', ['count' => $processed]));

        return self::SUCCESS;
    }
}
