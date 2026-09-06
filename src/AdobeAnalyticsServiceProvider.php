<?php

namespace JeffersonGoncalves\AdobeAnalytics;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdobeAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('adobe-analytics')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AdobeAnalyticsClient::class, function () {
            return new AdobeAnalyticsClient(
                clientId: (string) config('adobe-analytics.client_id'),
                clientSecret: (string) config('adobe-analytics.client_secret'),
                companyId: (string) config('adobe-analytics.company_id'),
                accessToken: config('adobe-analytics.access_token'),
                scope: (string) config('adobe-analytics.scope'),
                tokenSafetyMargin: (int) config('adobe-analytics.token_safety_margin', 60),
            );
        });

        $this->app->singleton(AdobeAnalytics::class, function ($app) {
            return new AdobeAnalytics($app->make(AdobeAnalyticsClient::class));
        });
    }
}
