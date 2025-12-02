<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;

describe('ActivityChartWidget', function () {
    beforeEach(function () {
        $this->widget = new ActivityChartWidget;
    });

    it('can instantiate widget', function () {
        expect($this->widget)->toBeInstanceOf(ActivityChartWidget::class);
    });

    it('returns correct heading from config', function () {
        config()->set('filament-activity-log.widgets.activity_chart.heading', 'Test Heading');

        expect($this->widget->getHeading())->toBe('Test Heading');
    });

    it('returns correct max height from config', function () {
        config()->set('filament-activity-log.widgets.activity_chart.max_height', '400px');

        $reflection = new \ReflectionClass($this->widget);
        $method = $reflection->getMethod('getMaxHeight');
        $method->setAccessible(true);

        expect($method->invoke($this->widget))->toBe('400px');
    });

    it('returns correct sort order from config', function () {
        config()->set('filament-activity-log.widgets.activity_chart.sort', 5);

        expect(ActivityChartWidget::getSort())->toBe(5);
    });

    it('can view when widgets enabled', function () {
        config()->set('filament-activity-log.widgets.enabled', true);
        config()->set('filament-activity-log.widgets.activity_chart.enabled', true);

        expect(ActivityChartWidget::canView())->toBeTrue();
    });

    it('cannot view when widgets disabled', function () {
        config()->set('filament-activity-log.widgets.enabled', false);

        expect(ActivityChartWidget::canView())->toBeFalse();
    });

    it('cannot view when activity chart disabled', function () {
        config()->set('filament-activity-log.widgets.enabled', true);
        config()->set('filament-activity-log.widgets.activity_chart.enabled', false);

        expect(ActivityChartWidget::canView())->toBeFalse();
    });

    it('returns line chart type by default', function () {
        config()->set('filament-activity-log.widgets.activity_chart.type', 'line');

        $reflection = new \ReflectionClass($this->widget);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        expect($method->invoke($this->widget))->toBe('line');
    });

    it('returns chart data with correct structure', function () {
        $reflection = new \ReflectionClass($this->widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);

        $data = $method->invoke($this->widget);

        expect($data)->toBeArray()
            ->and($data)->toHaveKeys(['datasets', 'labels'])
            ->and($data['datasets'])->toBeArray()
            ->and($data['labels'])->toBeArray();
    });

    it('returns chart options from config', function () {
        $expectedOptions = [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];

        config()->set('filament-activity-log.widgets.activity_chart.options', $expectedOptions);

        $reflection = new \ReflectionClass($this->widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        expect($method->invoke($this->widget))->toBe($expectedOptions);
    });
});
