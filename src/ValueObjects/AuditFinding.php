<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\ValueObjects;

final readonly class AuditFinding
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $title,
        public string $severity,
        public string $description,
        public array $context = [],
    ) {}
}
