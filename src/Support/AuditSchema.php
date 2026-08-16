<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Illuminate\Support\Facades\Schema;

class AuditSchema
{
    protected ?bool $hasRiskColumns = null;

    protected ?bool $hasRetentionHold = null;

    protected ?bool $hasIntegrityHash = null;

    protected ?bool $hasContextColumns = null;

    public function hasRiskColumns(): bool
    {
        return $this->hasRiskColumns ??= $this->hasColumns(['risk_score', 'risk_level']);
    }

    public function hasRetentionHold(): bool
    {
        return $this->hasRetentionHold ??= $this->hasColumns(['retention_hold']);
    }

    public function hasIntegrityHash(): bool
    {
        return $this->hasIntegrityHash ??= $this->hasColumns(['integrity_hash']);
    }

    public function hasContextColumns(): bool
    {
        return $this->hasContextColumns ??= $this->hasColumns(['request_id', 'ip_address']);
    }

    /**
     * @param  array<int, string>  $columns
     */
    protected function hasColumns(array $columns): bool
    {
        $modelClass = app(ActivityQuery::class)->modelClass();
        $model = new $modelClass;
        $schema = Schema::connection($model->getConnectionName());

        foreach ($columns as $column) {
            if (! $schema->hasColumn($model->getTable(), $column)) {
                return false;
            }
        }

        return true;
    }
}
