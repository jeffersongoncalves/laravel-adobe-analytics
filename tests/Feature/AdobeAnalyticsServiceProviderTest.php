<?php

use JeffersonGoncalves\AdobeAnalytics\AdobeAnalytics as AdobeAnalyticsManager;
use JeffersonGoncalves\AdobeAnalytics\Facades\AdobeAnalytics;

it('merges the default config', function () {
    expect(config('adobe-analytics.scope'))->toContain('read_organizations')
        ->and(config('adobe-analytics.token_safety_margin'))->toBe(60);
});

it('resolves the facade to the manager singleton', function () {
    expect(AdobeAnalytics::getFacadeRoot())->toBeInstanceOf(AdobeAnalyticsManager::class);
});

it('always resolves the same manager instance', function () {
    expect(app(AdobeAnalyticsManager::class))->toBe(app(AdobeAnalyticsManager::class));
});
