<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Policies\ActivityPolicy;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

describe('ActivityPolicy', function () {
    beforeEach(function () {
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->policy = new ActivityPolicy;
        $this->activity = new Activity;
    });

    describe('viewAny', function () {
        it('returns true when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->viewAny($this->user))->toBeTrue();
        });

        it('returns true when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.view_any', null);

            expect($this->policy->viewAny($this->user))->toBeTrue();
        });
    });

    describe('view', function () {
        it('returns true when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->view($this->user, $this->activity))->toBeTrue();
        });

        it('returns true when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.view', null);

            expect($this->policy->view($this->user, $this->activity))->toBeTrue();
        });
    });

    describe('create', function () {
        it('returns false when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->create($this->user))->toBeFalse();
        });

        it('returns false when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.create', null);

            expect($this->policy->create($this->user))->toBeFalse();
        });
    });

    describe('update', function () {
        it('returns false when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->update($this->user, $this->activity))->toBeFalse();
        });

        it('returns false when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.update', null);

            expect($this->policy->update($this->user, $this->activity))->toBeFalse();
        });
    });

    describe('delete', function () {
        it('returns false when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->delete($this->user, $this->activity))->toBeFalse();
        });

        it('returns false when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.delete', null);

            expect($this->policy->delete($this->user, $this->activity))->toBeFalse();
        });
    });

    describe('restore', function () {
        it('returns false when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->restore($this->user, $this->activity))->toBeFalse();
        });

        it('returns false when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.restore', null);

            expect($this->policy->restore($this->user, $this->activity))->toBeFalse();
        });
    });

    describe('forceDelete', function () {
        it('returns false when permissions are disabled', function () {
            config()->set('filament-activity-log.permissions.enabled', false);

            expect($this->policy->forceDelete($this->user, $this->activity))->toBeFalse();
        });

        it('returns false when permissions enabled but no permission configured', function () {
            config()->set('filament-activity-log.permissions.enabled', true);
            config()->set('filament-activity-log.permissions.force_delete', null);

            expect($this->policy->forceDelete($this->user, $this->activity))->toBeFalse();
        });
    });
});
