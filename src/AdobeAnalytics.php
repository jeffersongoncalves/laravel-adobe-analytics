<?php

namespace JeffersonGoncalves\AdobeAnalytics;

/**
 * Entry point exposing the Adobe Analytics 2.0 API surface used by this
 * package: report suites, dimensions, metrics, segments and report runs.
 */
class AdobeAnalytics
{
    public function __construct(protected AdobeAnalyticsClient $client) {}

    /** @return array<string, mixed> */
    public function reportSuites(): array
    {
        return $this->client->get('/reportsuites');
    }

    /** @return array<string, mixed> */
    public function dimensions(string $rsid): array
    {
        return $this->client->get('/dimensions', ['rsid' => $rsid]);
    }

    /** @return array<string, mixed> */
    public function metrics(string $rsid): array
    {
        return $this->client->get('/metrics', ['rsid' => $rsid]);
    }

    /** @return array<string, mixed> */
    public function segments(?string $rsid = null): array
    {
        return $this->client->get('/segments', array_filter(['rsid' => $rsid]));
    }

    /**
     * @param  string[]  $metrics  Metric ids, e.g. ["metrics/visits", "metrics/pageviews"].
     * @return array<string, mixed>
     */
    public function runReport(
        string $rsid,
        string $startDate,
        string $endDate,
        array $metrics,
        ?string $dimension = null,
    ): array {
        $body = [
            'rsid' => $rsid,
            'globalFilters' => [
                [
                    'type' => 'dateRange',
                    'dateRange' => "{$startDate}T00:00:00/{$endDate}T23:59:59",
                ],
            ],
            'metricContainer' => [
                'metrics' => array_map(fn (string $id) => ['id' => $id], $metrics),
            ],
        ];

        if ($dimension !== null) {
            $body['dimension'] = $dimension;
        }

        return $this->client->post('/reports', $body);
    }
}
