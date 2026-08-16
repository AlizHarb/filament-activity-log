<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Commands;

use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\AuditSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DoctorCommand extends Command
{
    protected $signature = 'filament-activity-log:doctor';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription((string) __('filament-activity-log::activity.commands.doctor.description'));
    }

    public function handle(ActivityQuery $activities, AuditSchema $auditSchema): int
    {
        $checks = [];
        $failed = false;

        try {
            $modelClass = $activities->modelClass();
            $model = new $modelClass;
            $tableExists = Schema::connection($model->getConnectionName())->hasTable($model->getTable());
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.activity_model'), true, $modelClass);
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.activity_table'), $tableExists, $model->getTable());
            $failed = ! $tableExists;
        } catch (Throwable $exception) {
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.activity_model'), false, $exception->getMessage());
            $failed = true;
        }

        if (! $failed) {
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.risk_metadata'), $auditSchema->hasRiskColumns(), __('filament-activity-log::activity.commands.doctor.details.publish_migrations'));
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.retention_holds'), $auditSchema->hasRetentionHold(), __('filament-activity-log::activity.commands.doctor.details.publish_migrations'));
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.integrity_signatures'), $auditSchema->hasIntegrityHash(), __('filament-activity-log::activity.commands.doctor.details.publish_migrations'));
            $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.investigation_indexes'), $auditSchema->hasContextColumns(), __('filament-activity-log::activity.commands.doctor.details.publish_migrations'));
        }

        $scope = config('filament-activity-log.query.scope');
        $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.global_query_scope'), $scope !== null, __('filament-activity-log::activity.commands.doctor.details.tenant_scope'));

        $cacheEnabled = (int) config('filament-activity-log.cache.ttl', 60) > 0;
        $cacheContextSafe = $scope === null || ! $cacheEnabled || config('filament-activity-log.cache.context_key') !== null;
        $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.scoped_cache'), $cacheContextSafe, __('filament-activity-log::activity.commands.doctor.details.cache_context'));

        $mutationsEnabled = config('filament-activity-log.mutations.enabled', false);
        $permissionsEnabled = config('filament-activity-log.permissions.enabled', false);
        $mutationAuthorizer = config('filament-activity-log.mutations.custom_authorization');
        $safeMutations = ! $mutationsEnabled || $permissionsEnabled || $mutationAuthorizer !== null;
        $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.mutation_authorization'), $safeMutations, __('filament-activity-log::activity.commands.doctor.details.mutation_authorization'));
        $failed = $failed || ! $safeMutations;

        $redactionEnabled = config('filament-activity-log.privacy.redaction.enabled', true);
        $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.display_redaction'), $redactionEnabled, __('filament-activity-log::activity.commands.doctor.details.recommended'));

        $bufferEnabled = config('activitylog.buffer.enabled', false);
        $metadataRequiresEvents = config('filament-activity-log.risk.enabled', true)
            || config('filament-activity-log.integrity.enabled', true)
            || config('filament-activity-log.alerts.enabled', false);
        $bufferSafe = ! $bufferEnabled || ! $metadataRequiresEvents;
        $checks[] = $this->check(__('filament-activity-log::activity.commands.doctor.checks.immediate_metadata'), $bufferSafe, __('filament-activity-log::activity.commands.doctor.details.disable_buffer'));
        $failed = $failed || ! $bufferSafe;

        $this->table([
            __('filament-activity-log::activity.commands.doctor.headers.check'),
            __('filament-activity-log::activity.commands.doctor.headers.status'),
            __('filament-activity-log::activity.commands.doctor.headers.details'),
        ], $checks);

        if ($failed) {
            $this->components->error(__('filament-activity-log::activity.commands.doctor.failed'));

            return self::FAILURE;
        }

        $this->components->info(__('filament-activity-log::activity.commands.doctor.passed'));

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function check(string $name, bool $passed, string $details): array
    {
        return [
            $name,
            $passed
                ? __('filament-activity-log::activity.commands.doctor.status.pass')
                : __('filament-activity-log::activity.commands.doctor.status.review'),
            $details,
        ];
    }
}
