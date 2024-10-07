<?php

declare(strict_types=1);

/**
 * This configuration file is used to manage the OpenID Connect (OIDC) settings for the Laravel application.
 * The 'default' key specifies the default authentication provider to be used.
 * The 'providers' key contains the configuration details for different OIDC providers.
 */

return [
    'default' => env('OPENID_DEFAULT_PROVIDER', 'okta'),

    'providers' => [
        // Configuration for Okta OpenID Connect provider
        'okta' => [
            'authorization' => [
                'client_id' => env('OKTA_CLIENT_ID'), // Client ID obtained from the Okta Developer Console
                'redirect_uri' => env('OKTA_REDIRECT_URI'), // Redirect URI registered in the Okta Developer Console
                'scopes' => explode(',', env('OKTA_SCOPES', 'openid, profile, email')), // Scopes requested from the Okta OpenID Connect provider
                'response_type' => 'code', // Response type for the Okta OpenID Connect provider
                'state' => csrf_token(), // CSRF token for the Okta OpenID Connect provider
            ],
            'access_token' => [
                'grant_type' => 'authorization_code', // Grant type for the Okta OpenID Connect provider
                'client_secret' => env('OKTA_CLIENT_SECRET'), // Client secret obtained from the Okta Developer Console
                'redirect_uri' => env('OKTA_REDIRECT_URI'), // Redirect URI registered in the Okta Developer Console
            ],
            'refresh_token' => [
                'grant_type' => 'refresh_token', // Grant type for the Okta OpenID Connect provider
                'client_id' => env('OKTA_CLIENT_ID'), // Client ID obtained from the Okta Developer Console
                'client_secret' => env('OKTA_CLIENT_SECRET'),
            ],
            'issuer' => env('OKTA_ISSUER'), // Issuer URL for Okta OpenID Connect
        ],
    ],
];
