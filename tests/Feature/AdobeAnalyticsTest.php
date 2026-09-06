<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException;
use JeffersonGoncalves\AdobeAnalytics\Facades\AdobeAnalytics;

beforeEach(function () {
    config(['adobe-analytics.access_token' => 'static-token']);
});

it('lists report suites', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['content' => [['rsid' => 'mysite']]])]);

    $result = AdobeAnalytics::reportSuites();

    expect($result['content'][0]['rsid'])->toBe('mysite');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/reportsuites'));
});

it('lists dimensions for a report suite', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response([['id' => 'variables/page']])]);

    $result = AdobeAnalytics::dimensions('mysite');

    expect($result[0]['id'])->toBe('variables/page');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/dimensions?rsid=mysite'));
});

it('lists metrics for a report suite', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response([['id' => 'metrics/visits']])]);

    $result = AdobeAnalytics::metrics('mysite');

    expect($result[0]['id'])->toBe('metrics/visits');
    Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/metrics?rsid=mysite'));
});

it('lists segments without a rsid filter', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['content' => []])]);

    AdobeAnalytics::segments();

    Http::assertSent(fn ($request) => str_ends_with((string) $request->url(), '/segments'));
});

it('lists segments filtered by rsid', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['content' => []])]);

    AdobeAnalytics::segments('mysite');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/segments?rsid=mysite'));
});

it('runs a report', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['rows' => []])]);

    AdobeAnalytics::runReport(
        rsid: 'mysite',
        startDate: '2026-01-01',
        endDate: '2026-01-31',
        metrics: ['metrics/visits', 'metrics/pageviews'],
        dimension: 'variables/page',
    );

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->method() === 'POST'
            && str_contains((string) $request->url(), '/reports')
            && $body['rsid'] === 'mysite'
            && $body['globalFilters'][0]['dateRange'] === '2026-01-01T00:00:00/2026-01-31T23:59:59'
            && $body['metricContainer']['metrics'][0]['id'] === 'metrics/visits'
            && $body['metricContainer']['metrics'][1]['id'] === 'metrics/pageviews'
            && $body['dimension'] === 'variables/page';
    });
});

it('runs a report without a dimension', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['rows' => []])]);

    AdobeAnalytics::runReport('mysite', '2026-01-01', '2026-01-31', ['metrics/visits']);

    Http::assertSent(fn ($request) => ! array_key_exists('dimension', $request->data()));
});

it('throws an AdobeAnalyticsException when the API returns an error', function () {
    Http::fake(['analytics.adobe.io/*' => Http::response(['error_code' => '400', 'message' => 'Invalid rsid'], 400)]);

    AdobeAnalytics::reportSuites();
})->throws(AdobeAnalyticsException::class, 'Invalid rsid');
