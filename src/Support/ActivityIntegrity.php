<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Exceptions\IntegrityException;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityIntegrity
{
    public function hash(Activity $activity): string
    {
        $key = (string) (config('filament-activity-log.integrity.key') ?: config('app.key'));

        if ($key === '') {
            throw new IntegrityException('integrity.missing_key');
        }

        $payload = $this->canonicalize($activity->only([
            $activity->getKeyName(),
            'log_name',
            'description',
            'subject_type',
            'subject_id',
            'causer_type',
            'causer_id',
            'event',
            'properties',
            'attribute_changes',
            'batch_uuid',
            'risk_score',
            'risk_level',
            'retention_hold',
            'request_id',
            'ip_address',
            'created_at',
            'updated_at',
        ]));

        return hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), $key);
    }

    public function verify(Activity $activity): bool
    {
        $storedHash = $activity->getAttribute('integrity_hash');

        return is_string($storedHash) && $storedHash !== '' && hash_equals($storedHash, $this->hash($activity));
    }

    protected function canonicalize(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        foreach ($value as $key => $nestedValue) {
            $value[$key] = $this->canonicalize($nestedValue);
        }

        return $value;
    }
}
