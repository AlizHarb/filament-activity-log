<?php

namespace AlizHarb\ActivityLog\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * A minimal Authenticatable implementation that does NOT extend
 * Illuminate\Foundation\Auth\User. Used to verify that ActivityPolicy
 * accepts any Authenticatable rather than requiring the concrete base class.
 */
class ContractOnlyUser implements Authenticatable
{
    public function __construct(
        private readonly string $id = '1',
    ) {}

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
