<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Widgets\LatestActivityWidget;

describe('LatestActivityWidget', function () {
    beforeEach(function () {
        $this->widget = new LatestActivityWidget;
    });

    it('can instantiate widget', function () {
        expect($this->widget)->toBeInstanceOf(LatestActivityWidget::class);
    });

    it('returns correct heading from config', function () {
        config()->set('filament-activity-log.widgets.latest_activity.heading', 'Recent Activities');

        expect($this->widget->getHeading())->toBe('Recent Activities');
    });

    it('returns translation when heading not configured', function () {
        config()->set('filament-activity-log.widgets.latest_activity.heading', null);

        expect($this->widget->getHeading())->toBe(__('filament-activity-log::activity.widgets.latest_activity'));
    });

    it('returns correct sort order from config', function () {
        config()->set('filament-activity-log.widgets.latest_activity.sort', 3);

        expect(LatestActivityWidget::getSort())->toBe(3);
    });

    it('can view when widgets enabled', function () {
        config()->set('filament-activity-log.widgets.enabled', true);
        config()->set('filament-activity-log.widgets.latest_activity.enabled', true);

        expect(LatestActivityWidget::canView())->toBeTrue();
    });

    it('cannot view when widgets disabled', function () {
        config()->set('filament-activity-log.widgets.enabled', false);

        expect(LatestActivityWidget::canView())->toBeFalse();
    });

    it('cannot view when latest activity widget disabled', function () {
        config()->set('filament-activity-log.widgets.enabled', true);
        config()->set('filament-activity-log.widgets.latest_activity.enabled', false);

        expect(LatestActivityWidget::canView())->toBeFalse();
    });

});
