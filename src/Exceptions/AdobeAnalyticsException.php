<?php

namespace JeffersonGoncalves\AdobeAnalytics\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class AdobeAnalyticsException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $errorBody = [];

    public static function fromResponse(Response $response): self
    {
        $body = (array) ($response->json() ?? []);

        // Adobe Analytics API errors use "message"; Adobe IMS (OAuth token)
        // errors use "error"/"error_description" instead.
        $message = $body['message']
            ?? $body['error_description']
            ?? $body['error']
            ?? "Adobe Analytics API error (HTTP {$response->status()}).";

        $exception = new self((string) $message, $response->status());
        $exception->errorBody = $body;

        return $exception;
    }

    /** @return array<string, mixed> */
    public function errorBody(): array
    {
        return $this->errorBody;
    }
}
