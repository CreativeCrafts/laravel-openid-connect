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

## Testing

```bash
This package is built with a focus on quality and reliability. I am currently working on adding more tests to ensure that the package works as expected.


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
