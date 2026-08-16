<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Exceptions;

use RuntimeException;
use Throwable;

class ActivityLogException extends RuntimeException
{
    /**
     * @param  array<string, scalar>  $replacements
     */
    public function __construct(
        public readonly string $translationKey,
        public readonly array $replacements = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            (string) __("filament-activity-log::activity.exceptions.{$translationKey}", $replacements),
            previous: $previous,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'activity_log_error' => $this->translationKey,
            'activity_log_error_context' => $this->replacements,
        ];
    }
}
