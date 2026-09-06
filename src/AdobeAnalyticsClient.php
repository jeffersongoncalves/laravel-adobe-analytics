<?php

namespace JeffersonGoncalves\AdobeAnalytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException;

/**
 * Thin wrapper around Laravel's Http client for the Adobe Analytics 2.0 API,
 * including OAuth Server-to-Server token acquisition and caching.
 *
 * ponytail: Http::retry() already covers transient-failure retries if ever
 * needed later — no custom retry/backoff layer built here speculatively.
 */
class AdobeAnalyticsClient
{
    protected const IMS_TOKEN_URL = 'https://ims-na1.adobelogin.com/ims/token/v3';

    protected const TOKEN_CACHE_KEY = 'adobe-analytics.access_token';

    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $companyId,
        protected ?string $accessToken,
        protected string $scope,
        protected int $tokenSafetyMargin = 60,
    ) {}

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    /** @param array<string, mixed> $body */
    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, $body);
    }

    /** @param array<string, mixed> $data */
    protected function request(string $method, string $path, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->token(),
            'x-api-key' => $this->clientId,
            'x-proxy-global-company-id' => $this->companyId,
            'Content-Type' => 'application/json',
        ])
            ->baseUrl("https://analytics.adobe.io/api/{$this->companyId}")
            ->{$method}($path, $data);

        if ($response->failed()) {
            throw AdobeAnalyticsException::fromResponse($response);
        }

        return (array) ($response->json() ?? []);
    }

    protected function token(): string
    {
        if (filled($this->accessToken)) {
            return $this->accessToken;
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        return $this->fetchToken();
    }

    protected function fetchToken(): string
    {
        $response = Http::asForm()->post(self::IMS_TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => $this->scope,
        ]);

        if ($response->failed()) {
            throw AdobeAnalyticsException::fromResponse($response);
        }

        $body = (array) ($response->json() ?? []);
        $accessToken = (string) ($body['access_token'] ?? '');

        // Adobe IMS returns expires_in in milliseconds, unlike the OAuth
        // convention of seconds used by most providers.
        $expiresInMs = (int) ($body['expires_in'] ?? 0);
        $ttl = max(intdiv($expiresInMs, 1000) - $this->tokenSafetyMargin, 60);

        Cache::put(self::TOKEN_CACHE_KEY, $accessToken, $ttl);

        return $accessToken;
    }
}
