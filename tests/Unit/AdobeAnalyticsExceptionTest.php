<?php

use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\Response as HttpResponse;
use JeffersonGoncalves\AdobeAnalytics\Exceptions\AdobeAnalyticsException;

function fakeAdobeAnalyticsResponse(int $status, array $body): Response
{
    $psr = new GuzzleHttp\Psr7\Response($status, [], json_encode($body));

    return new HttpResponse($psr);
}

it('builds the exception message from the response "message" field', function () {
    $response = fakeAdobeAnalyticsResponse(400, ['error_code' => '400', 'message' => 'Invalid rsid']);

    $exception = AdobeAnalyticsException::fromResponse($response);

    expect($exception->getMessage())->toBe('Invalid rsid')
        ->and($exception->getCode())->toBe(400)
        ->and($exception->errorBody())->toBe(['error_code' => '400', 'message' => 'Invalid rsid']);
});

it('falls back to the IMS "error_description" field', function () {
    $response = fakeAdobeAnalyticsResponse(400, ['error' => 'invalid_client', 'error_description' => 'Client authentication failed']);

    $exception = AdobeAnalyticsException::fromResponse($response);

    expect($exception->getMessage())->toBe('Client authentication failed');
});

it('falls back to the IMS "error" field when no description is present', function () {
    $response = fakeAdobeAnalyticsResponse(400, ['error' => 'invalid_client']);

    $exception = AdobeAnalyticsException::fromResponse($response);

    expect($exception->getMessage())->toBe('invalid_client');
});

it('falls back to a generic message when the body has no known error keys', function () {
    $response = fakeAdobeAnalyticsResponse(500, []);

    $exception = AdobeAnalyticsException::fromResponse($response);

    expect($exception->getMessage())->toBe('Adobe Analytics API error (HTTP 500).');
});
