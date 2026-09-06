<div class="filament-hidden">

![Laravel Adobe Analytics](https://raw.githubusercontent.com/jeffersongoncalves/laravel-adobe-analytics/main/art/jeffersongoncalves-laravel-adobe-analytics.png)

</div>

# Laravel Adobe Analytics

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-adobe-analytics.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-adobe-analytics)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-adobe-analytics/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-adobe-analytics/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-adobe-analytics/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-adobe-analytics/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-adobe-analytics.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-adobe-analytics)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-adobe-analytics.svg?style=flat-square)](LICENSE.md)

A PHP/Laravel client for the [Adobe Analytics 2.0](https://developer.adobe.com/analytics-apis/docs/2.0/) REST API. Covers report suites, dimensions, metrics, segments and report runs through a simple, typed API built on Laravel's `Http` client, with OAuth Server-to-Server (`client_credentials`) token acquisition and caching handled for you.

## Features

- Report suites: list all report suites available to your company
- Dimensions and metrics: list the available dimensions/metrics for a report suite
- Segments: list segments, optionally filtered by report suite
- Reports: run a report for a date range, a set of metrics and an optional dimension
- OAuth Server-to-Server (`client_credentials`) token acquisition, cached automatically and refreshed on expiry
- Optional static `access_token` override that skips the OAuth flow entirely (handy for testing or when a token is already managed elsewhere)
- Throws `AdobeAnalyticsException` (with the original API error body) on any non-2xx response

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-adobe-analytics
```

Publish the config file:

```bash
php artisan vendor:publish --tag=adobe-analytics-config
```

### Using OAuth Server-to-Server (recommended)

Create a [Server-to-Server OAuth credential](https://developer.adobe.com/developer-console/docs/guides/authentication/ServerToServerAuthentication/) in the Adobe Developer Console for the Adobe Analytics API, then set:

```env
ADOBE_CLIENT_ID=your-client-id
ADOBE_CLIENT_SECRET=your-client-secret
ADOBE_COMPANY_ID=your-global-company-id
```

The package will exchange these credentials for an access token on first use, cache it, and transparently fetch a new one once it expires.

### Using a static access token

If you already manage a token elsewhere (or for local testing), skip the OAuth flow entirely by setting:

```env
ADOBE_ACCESS_TOKEN=your-precomputed-access-token
ADOBE_COMPANY_ID=your-global-company-id
```

When `ADOBE_ACCESS_TOKEN` is set, it is used as-is on every request and `ADOBE_CLIENT_ID`/`ADOBE_CLIENT_SECRET` are not required.

## Configuration

```php
// config/adobe-analytics.php
return [
    'client_id' => env('ADOBE_CLIENT_ID', ''),
    'client_secret' => env('ADOBE_CLIENT_SECRET', ''),
    'company_id' => env('ADOBE_COMPANY_ID', ''),
    'access_token' => env('ADOBE_ACCESS_TOKEN'),
    'scope' => env('ADOBE_SCOPE', 'openid,AdobeID,additional_info.projectedProductContext,read_organizations,additional_info.roles'),
    'token_safety_margin' => env('ADOBE_TOKEN_SAFETY_MARGIN', 60),
];
```

## Usage

The package is resolved via the `AdobeAnalytics` facade or by injecting `JeffersonGoncalves\AdobeAnalytics\AdobeAnalytics`.

### Report suites

```php
use JeffersonGoncalves\AdobeAnalytics\Facades\AdobeAnalytics;

$reportSuites = AdobeAnalytics::reportSuites();
```

### Dimensions

```php
$dimensions = AdobeAnalytics::dimensions('mysiterunsid');
```

### Metrics

```php
$metrics = AdobeAnalytics::metrics('mysiterunsid');
```

### Segments

```php
// All segments
$segments = AdobeAnalytics::segments();

// Segments available to a specific report suite
$segments = AdobeAnalytics::segments('mysiterunsid');
```

### Running a report

```php
$report = AdobeAnalytics::runReport(
    rsid: 'mysiterunsid',
    startDate: '2026-01-01',
    endDate: '2026-01-31',
    metrics: ['metrics/visits', 'metrics/pageviews'],
    dimension: 'variables/page', // optional
);
```

### Error handling

Any non-2xx API response (including OAuth token failures) throws `JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException`, which exposes the decoded error body:

```php
use JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException;

try {
    AdobeAnalytics::dimensions('invalid-rsid');
} catch (AdobeAnalyticsException $e) {
    logger()->error($e->getMessage(), $e->errorBody());
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
