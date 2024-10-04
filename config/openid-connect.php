<?php

declare(strict_types=1);

/**
 * This configuration file is used to manage the OpenID Connect (OIDC) settings for the Laravel application.
 * The 'default' key specifies the default authentication provider to be used.
 * The 'providers' key contains the configuration details for different OIDC providers.
 */

return [
    'default' => env('OPENID_DEFAULT_PROVIDER', 'google'),

    'providers' => [
        // Configuration for Google OpenID Connect provider
        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            // Client ID obtained from the Google Cloud Console
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            // Client secret obtained from the Google Cloud Console
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            // Redirect URI registered in the Google Cloud Console
            'issuer' => 'https://accounts.google.com',
            // Issuer URL for Google OpenID Connect
            'scopes' => explode(',', env('GOOGLE_SCOPES', 'openid profile email')),
            // Scopes requested from the Google OpenID Connect provider
        ],

        // Configuration for Okta OpenID Connect provider
        'okta' => [
            'client_id' => env('OKTA_CLIENT_ID'),
            // Client ID obtained from the Okta Developer Console
            'client_secret' => env('OKTA_CLIENT_SECRET'),
            // Client secret obtained from the Okta Developer Console
            'redirect_uri' => env('OKTA_REDIRECT_URI'),
            // Redirect URI registered in the Okta Developer Console
            'issuer' => env('OKTA_ISSUER'),
            // Issuer URL for Okta OpenID Connect
            'scopes' => explode(',', env('OKTA_SCOPES', 'openid profile email')),
            // Scopes requested from the Okta OpenID Connect provider
        ],

        // Configuration for Azure Active Directory OpenID Connect provider
        'azure' => [
            'client_id' => env('AZURE_CLIENT_ID'),
            // Client ID obtained from the Azure Active Directory App Registration
            'client_secret' => env('AZURE_CLIENT_SECRET'),
            // Client secret obtained from the Azure Active Directory App Registration
            'redirect_uri' => env('AZURE_REDIRECT_URI'),
            // Redirect URI registered in the Azure Active Directory App Registration
            'issuer' => env('AZURE_ISSUER'),
            // Issuer URL for Azure Active Directory OpenID Connect
            'scopes' => explode(',', env('AZURE_SCOPES', 'openid profile email')),
            // Scopes requested from the Azure Active Directory OpenID Connect provider
        ],
    ],
];
