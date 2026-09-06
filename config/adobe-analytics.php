<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The Adobe Developer Console API Client ID for your Adobe Analytics
    | Server-to-Server OAuth credential.
    |
    */
    'client_id' => env('ADOBE_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    */
    'client_secret' => env('ADOBE_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Company ID
    |--------------------------------------------------------------------------
    |
    | Your Adobe Analytics global company id, sent as the
    | x-proxy-global-company-id header and used to build the API base URL.
    |
    */
    'company_id' => env('ADOBE_COMPANY_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Static Access Token (optional override)
    |--------------------------------------------------------------------------
    |
    | If set, this token is used as-is on every request and the OAuth
    | client_credentials flow (and its token cache) is skipped entirely.
    | Useful for testing or when a token is already managed elsewhere.
    |
    */
    'access_token' => env('ADOBE_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Scope
    |--------------------------------------------------------------------------
    |
    | Scope requested when exchanging client credentials for an access token
    | at https://ims-na1.adobelogin.com/ims/token/v3.
    |
    */
    'scope' => env('ADOBE_SCOPE', 'openid,AdobeID,additional_info.projectedProductContext,read_organizations,additional_info.roles'),

    /*
    |--------------------------------------------------------------------------
    | Token Safety Margin
    |--------------------------------------------------------------------------
    |
    | Number of seconds subtracted from the token's expires_in when computing
    | the cache TTL, so a cached token is never used right up to the moment
    | it actually expires.
    |
    */
    'token_safety_margin' => env('ADOBE_TOKEN_SAFETY_MARGIN', 60),

];
