# LaravelOpenIdConnect

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativecrafts/laravel-openid-connect.svg?style=flat-square)](https://packagist.org/packages/creativecrafts/laravel-openid-connect)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/creativecrafts/laravel-openid-connect/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/creativecrafts/laravel-openid-connect/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/creativecrafts/laravel-openid-connect/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/creativecrafts/laravel-openid-connect/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/creativecrafts/laravel-openid-connect.svg?style=flat-square)](https://packagist.org/packages/creativecrafts/laravel-openid-connect)

The **LaravelOpenIdConnect** package offers seamless integration of OpenID Connect authentication for Laravel applications. It allows your app to securely authenticate users via OpenID Connect providers, enabling effortless access token management, user information retrieval, and token refreshment. With a clean and straightforward API, this package abstracts the complexity of managing OAuth 2.0 flows behind the scenes, so developers can focus on building applications rather than managing authentication processes.
This is package was forked from "jumbojett/OpenID-Connect-PHP" and modified to work the Laravel way. 
 - Special thanks to [jumbojett/OpenID-Connect-PHP](https://github.com/jumbojett/OpenID-Connect-PHP).

## Key Features

- **Authorization URL Generation**: Easily generate the URL to redirect users for OpenID Connect authentication.
- **Access Token Retrieval**: Obtain access tokens using an authorization code in compliance with OAuth 2.0 standards.
- **User Information**: Retrieve user information (claims) from the OpenID Connect provider with ease.
- **Token Refreshing**: Automatically refresh tokens when they expire using the refresh token.
- **Customizable Configurations**: Define multiple OpenID Connect providers in the configuration for multi-provider support.

## Installation

1. Install the package via Composer:

```bash
composer require creativecrafts/laravel-openid-connect
```

Next, publish the package's configuration file:

```bash
php artisan vendor:publish  --tag="openid-connect-config"
```

This will create a config/openid-connect.php file in your Laravel project.
This is the contents of the published config file:

```php
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
```
```
EXAMPLE_CLIENT_ID=your-client-id
EXAMPLE_CLIENT_SECRET=your-client-secret
EXAMPLE_REDIRECT_URI=https://your-app.com/callback
```

## Usage

```php
1.Configure the providers in the config/openid-connect.php file.

2.Authenticate users by redirecting them to the provider’s authorization page.:

use CreativeCrafts\LaravelOpenidConnect\LaravelOpenIdConnect;

$openIdConnect = new LaravelOpenIdConnect();
/** @var bool $authorisation */
$authorisation = $openIdConnect
    ->acceptProvider('providerName'))
    ->authenticate();
    
2.Get User Information:
Once the user is authenticated, retrieve user information from the OpenID Connect provider.
// optionally pass the $authorisation attribute you want to retrieve
if ($authorisation) {
    $userInfo = $openIdConnect->retrieveUserInfo();
}

Error Handling:
If the configuration for the provider is missing or misconfigured, the package throws an InvalidProviderConfigurationException. Ensure that all necessary configurations, such as the issuer URL, client ID, and client secret, are correctly set.
```

## State and session management

- This package uses a state-scoped bundle stored in session or cache as the single source of truth for transient values (nonce and PKCE code_verifier). Direct per-key usage (e.g. separate nonce/state keys) is deprecated; rely on saveStateBundle()/loadStateBundle() behavior.
- Multiple outstanding authorizations per session are supported by scoping bundles to the state value.
- On successful or failed callback processing, the bundle is cleared and a short-lived tombstone is written to prevent replay of the same state.
- When using session storage, ensure cookies are configured with Secure, HttpOnly, and an appropriate SameSite setting for your deployment.

### Potential pitfalls and guidance
- Regenerating the session ID mid-flow: If your app regenerates IDs aggressively (e.g., on each request), the sid check will reject the callback. Consider deferring regeneration until after OIDC completes, or disable mid-request regeneration for the callback route.
- Distributed systems: When using cache storage (`OPENID_CONNECT_STORAGE=cache`), ensure all app instances point to the same cache backend and share a common prefix (`OPENID_CONNECT_KEY_PREFIX`). Also ensure clock skew is tolerable and cache TTLs are sufficient across nodes.
- Long consent screens: Increase `OPENID_CONNECT_CACHE_TTL` when using cache storage, or prefer session storage if your session persistence is more reliable during long user interactions.
- Legacy keys: Direct usage of `nonce`/`state`/`code_verifier` per-key in storage is deprecated. The manager still cleans legacy keys for backwards compatibility, but your integration should rely on the state-bundled APIs.

### Storage configuration quick reference
- OPENID_CONNECT_STORAGE: `session` (default) | `cache` | `none`
- OPENID_CONNECT_KEY_PREFIX: Key prefix for both session and cache storages.
- OPENID_CONNECT_CACHE_STORE: Named Laravel cache store to use when storage is `cache`.
- OPENID_CONNECT_CACHE_TTL: Integer seconds; increase for longer flows or set to null in config to store forever (not recommended for security).

## Testing

```bash
This package is built with a focus on quality and reliability.

To run the tests, use the following command:
./vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Godspower Oduose](https://github.com/rockblings)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
