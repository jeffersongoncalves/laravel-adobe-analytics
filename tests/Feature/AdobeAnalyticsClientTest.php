<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\AdobeAnalytics\AdobeAnalyticsClient;
use JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException;

beforeEach(function () {
    Cache::flush();
});

it('fetches an access token via client_credentials and caches it', function () {
    Http::fake([
        'ims-na1.adobelogin.com/*' => Http::response(['access_token' => 'fetched-token', 'expires_in' => 86400000]),
        'analytics.adobe.io/*' => Http::response(['reportSuites' => []]),
    ]);

    $client = app(AdobeAnalyticsClient::class);
    $client->get('/reportsuites');
    $client->get('/reportsuites');

    // One token request + two API requests; the second get() reuses the cached token.
    Http::assertSentCount(3);

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'ims-na1.adobelogin.com')
        && $request['grant_type'] === 'client_credentials'
        && $request['client_id'] === 'test-client-id'
        && $request['client_secret'] === 'test-client-secret'
        && $request['scope'] === config('adobe-analytics.scope'));
});

it('uses a static access token and skips the OAuth flow entirely', function () {
    Http::fake([
        'analytics.adobe.io/*' => Http::response(['reportSuites' => []]),
    ]);

    $client = new AdobeAnalyticsClient(
        clientId: 'test-client-id',
        clientSecret: 'test-client-secret',
        companyId: 'test-company',
        accessToken: 'static-token',
        scope: 'openid',
    );

    $client->get('/reportsuites');

    Http::assertNotSent(fn ($request) => str_contains((string) $request->url(), 'ims-na1.adobelogin.com'));
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer static-token'));
});

it('sends the required headers on every request', function () {
    Http::fake([
        'ims-na1.adobelogin.com/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600000]),
        'analytics.adobe.io/*' => Http::response(['ok' => true]),
    ]);

    app(AdobeAnalyticsClient::class)->get('/reportsuites');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'analytics.adobe.io')
        && $request->hasHeader('Authorization', 'Bearer tok')
        && $request->hasHeader('x-api-key', 'test-client-id')
        && $request->hasHeader('x-proxy-global-company-id', 'test-company')
        && $request->hasHeader('Content-Type', 'application/json'));
});

it('throws an AdobeAnalyticsException when the token request fails', function () {
    Http::fake([
        'ims-na1.adobelogin.com/*' => Http::response(['error' => 'invalid_client', 'error_description' => 'Client authentication failed'], 400),
    ]);

    app(AdobeAnalyticsClient::class)->get('/reportsuites');
})->throws(AdobeAnalyticsException::class, 'Client authentication failed');
