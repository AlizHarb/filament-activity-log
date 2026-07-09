<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

class ActivityLogRedactor
{
    public static function redactedValue(): string
    {
        return (string) config('filament-activity-log.privacy.redacted_value', '[redacted]');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function redact(array $data): array
    {
        if (! config('filament-activity-log.privacy.redaction.enabled', true)) {
            return $data;
        }

        return static::redactArray($data);
    }

    public static function redactValue(string $key, mixed $value): mixed
    {
        if (! config('filament-activity-log.privacy.redaction.enabled', true)) {
            return $value;
        }

        if (static::isSensitiveKey($key)) {
            return static::redactedValue();
        }

        if (is_array($value)) {
            return static::redactArray($value);
        }

        return $value;
    }

    public static function isSensitiveKey(string $key): bool
    {
        $normalizedKey = strtolower(str_replace(['-', ' '], '_', $key));

        foreach (static::sensitiveFields() as $field) {
            $normalizedField = strtolower(str_replace(['-', ' '], '_', (string) $field));

            if ($normalizedKey === $normalizedField || str_ends_with($normalizedKey, '.'.$normalizedField)) {
                return true;
            }
        }

        foreach (static::sensitivePatterns() as $pattern) {
            if (@preg_match((string) $pattern, $normalizedKey) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public static function sensitiveFields(): array
    {
        return config('filament-activity-log.privacy.redaction.fields', [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'api_token',
            'access_token',
            'refresh_token',
            'secret',
            'api_key',
            'private_key',
            'remember_token',
        ]);
    }

    /**
     * @return array<int, string>
     */
    public static function sensitivePatterns(): array
    {
        return config('filament-activity-log.privacy.redaction.patterns', [
            '/(^|_)(password|token|secret|key)$/',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function redactArray(array $data, string $prefix = ''): array
    {
        $redacted = [];

        foreach ($data as $key => $value) {
            $stringKey = (string) $key;
            $path = $prefix === '' ? $stringKey : $prefix.'.'.$stringKey;

            if (static::isSensitiveKey($path)) {
                $redacted[$key] = static::redactedValue();

                continue;
            }

            $redacted[$key] = is_array($value)
                ? static::redactArray($value, $path)
                : $value;
        }

        return $redacted;
    }
}
