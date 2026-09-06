<?php

namespace JeffersonGoncalves\AdobeAnalytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\AdobeAnalytics\AdobeAnalytics
 *
 * @method static array reportSuites()
 * @method static array dimensions(string $rsid)
 * @method static array metrics(string $rsid)
 * @method static array segments(?string $rsid = null)
 * @method static array runReport(string $rsid, string $startDate, string $endDate, array $metrics, ?string $dimension = null)
 */
class AdobeAnalytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JeffersonGoncalves\AdobeAnalytics\AdobeAnalytics::class;
    }
}
