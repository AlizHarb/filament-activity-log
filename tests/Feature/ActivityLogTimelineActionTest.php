<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Actions\ActivityLogTimelineTableAction;

describe('ActivityLogTimelineTableAction', function () {
    beforeEach(function () {
        $this->action = ActivityLogTimelineTableAction::make();
    });

    it('can create action instance', function () {
        expect($this->action)->toBeInstanceOf(ActivityLogTimelineTableAction::class);
    });

    it('has default name of timeline', function () {
        $action = ActivityLogTimelineTableAction::make();

        expect($action->getName())->toBe('timeline');
    });

    it('can set custom icons', function () {
        $icons = [
            'created' => 'heroicon-m-plus-circle',
            'updated' => 'heroicon-m-pencil-square',
            'deleted' => 'heroicon-m-trash',
        ];

        $action = ActivityLogTimelineTableAction::make()->icons($icons);

        expect($action->getIcons())->toBe($icons);
    });

    it('can set custom colors', function () {
        $colors = [
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
        ];

        $action = ActivityLogTimelineTableAction::make()->colors($colors);

        expect($action->getColors())->toBe($colors);
    });

    it('returns empty arrays for icons and colors by default', function () {
        $action = ActivityLogTimelineTableAction::make();

        expect($action->getIcons())->toBeArray()->toBeEmpty()
            ->and($action->getColors())->toBeArray()->toBeEmpty();
    });

    it('can chain icon and color configuration', function () {
        $icons = ['created' => 'heroicon-m-plus'];
        $colors = ['created' => 'success'];

        $action = ActivityLogTimelineTableAction::make()
            ->icons($icons)
            ->colors($colors);

        expect($action->getIcons())->toBe($icons)
            ->and($action->getColors())->toBe($colors);
    });
});
