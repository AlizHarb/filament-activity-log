<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;
use AlizHarb\ActivityLog\Widgets\LatestActivityWidget;

describe('ActivityLogPlugin', function () {
    it('can create plugin instance', function () {
        $plugin = ActivityLogPlugin::make();

        expect($plugin)->toBeInstanceOf(ActivityLogPlugin::class);
    });

    it('has correct plugin id', function () {
        $plugin = ActivityLogPlugin::make();

        expect($plugin->getId())->toBe('filament-activity-log');
    });

    it('can set and get label', function () {
        $plugin = ActivityLogPlugin::make()
            ->label('Custom Label');

        expect($plugin->getLabel())->toBe('Custom Label');
    });

    it('can set label with closure', function () {
        $plugin = ActivityLogPlugin::make()
            ->label(fn () => 'Dynamic Label');

        expect($plugin->getLabel())->toBe('Dynamic Label');
    });

    it('returns default label when not set', function () {
        $plugin = ActivityLogPlugin::make();

        expect($plugin->getLabel())->toBe(__('filament-activity-log::activity.label'));
    });

    it('can set and get plural label', function () {
        $plugin = ActivityLogPlugin::make()
            ->pluralLabel('Custom Plural');

        expect($plugin->getPluralLabel())->toBe('Custom Plural');
    });

    it('returns default plural label when not set', function () {
        $plugin = ActivityLogPlugin::make();

        expect($plugin->getPluralLabel())->toBe(__('filament-activity-log::activity.plural_label'));
    });

    it('can set and get navigation group', function () {
        $plugin = ActivityLogPlugin::make()
            ->navigationGroup('System');

        expect($plugin->getNavigationGroup())->toBe('System');
    });

    it('can set and get navigation icon', function () {
        $plugin = ActivityLogPlugin::make()
            ->navigationIcon('heroicon-o-document-text');

        expect($plugin->getNavigationIcon())->toBe('heroicon-o-document-text');
    });

    it('returns default navigation icon when not set', function () {
        $plugin = ActivityLogPlugin::make();

        expect($plugin->getNavigationIcon())->toBe(config('filament-activity-log.resource.navigation_icon'));
    });

    it('can set and get navigation sort', function () {
        $plugin = ActivityLogPlugin::make()
            ->navigationSort(10);

        expect($plugin->getNavigationSort())->toBe(10);
    });

    it('can set and get navigation count badge', function () {
        $plugin = ActivityLogPlugin::make()
            ->navigationCountBadge('5');

        expect($plugin->getNavigationCountBadge())->toBe('5');
    });

    it('returns widgets when enabled in config', function () {
        config()->set('filament-activity-log.widgets.enabled', true);
        config()->set('filament-activity-log.widgets.widgets', [
            ActivityChartWidget::class,
            LatestActivityWidget::class,
        ]);

        $plugin = ActivityLogPlugin::make();
        $widgets = $plugin->getWidgets();

        expect($widgets)->toBeArray()
            ->and($widgets)->toHaveCount(2)
            ->and($widgets)->toContain(ActivityChartWidget::class)
            ->and($widgets)->toContain(LatestActivityWidget::class);
    });

    it('returns empty array when widgets disabled in config', function () {
        config()->set('filament-activity-log.widgets.enabled', false);

        $plugin = ActivityLogPlugin::make();
        $widgets = $plugin->getWidgets();

        expect($widgets)->toBeArray()
            ->and($widgets)->toBeEmpty();
    });
});
