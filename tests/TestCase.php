<?php

namespace JeffersonGoncalves\AdobeAnalytics\Tests;

use JeffersonGoncalves\AdobeAnalytics\AdobeAnalyticsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AdobeAnalyticsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('adobe-analytics.client_id', 'test-client-id');
        $app['config']->set('adobe-analytics.client_secret', 'test-client-secret');
        $app['config']->set('adobe-analytics.company_id', 'test-company');
        $app['config']->set('adobe-analytics.access_token', null);
        $app['config']->set('cache.default', 'array');
    }
}
