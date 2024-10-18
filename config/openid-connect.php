<?php

declare(strict_types=1);

/**
 * This configuration file is used to manage the OpenID Connect (OIDC) settings for the Laravel application.
 * The 'default' key specifies the default authentication provider to be used.
 * The 'providers' key contains the configuration details for different OIDC providers.
 */

return [
    /** The default authentication provider to be used. if a provider is not specified */
    'default_provider' => env('OPENID_CONNECT_DEFAULT_PROVIDER', ''),
    /** The default redirect URL to be used. if a provider redirect url is not provided. */
    'default_redirect_url' => env(
        'OPENID_CONNECT_DEFAULT_REDIRECT_URI',
        ''
    ),
    /** List of providers. */
    'providers' => [
        'example' => [
            'provider_url' => env('EXAMPLE_PROVIDER_URI'),
            'issuer' => env('EXAMPLE_ISSUER_URI'),
            'client_id' => env('EXAMPLE_CLIENT_ID'),
            'client_secret' => env('EXAMPLE_CLIENT_SECRET'),
            'scopes' => ['openid', 'email', 'profile'],
            'response_type' => 'code',
            'redirect_url' => env('EXAMPLE_REDIRECT_URI'),
            "token_endpoint_auth_methods_supported" => [
                "client_secret_post",
                "client_secret_basic",
            ],
        ],
    ],
];
