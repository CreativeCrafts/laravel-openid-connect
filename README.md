# LaravelOpenIdConnect

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativecrafts/laravel-openid-connect.svg?style=flat-square)](https://packagist.org/packages/creativecrafts/laravel-openid-connect)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/creativecrafts/laravel-openid-connect/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/creativecrafts/laravel-openid-connect/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/creativecrafts/laravel-openid-connect/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/creativecrafts/laravel-openid-connect/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/creativecrafts/laravel-openid-connect.svg?style=flat-square)](https://packagist.org/packages/creativecrafts/laravel-openid-connect)

The **LaravelOpenIdConnect** package offers seamless integration of OpenID Connect authentication for Laravel applications. It allows your app to securely authenticate users via OpenID Connect providers, enabling effortless access token management, user information retrieval, and token refreshment. With a clean and straightforward API, this package abstracts the complexity of managing OAuth 2.0 flows behind the scenes, so developers can focus on building applications rather than managing authentication processes.

## Key Features

- **Authorization URL Generation**: Easily generate the URL to redirect users for OpenID Connect authentication.
- **Access Token Retrieval**: Obtain access tokens using an authorization code in compliance with OAuth 2.0 standards.
- **User Information**: Retrieve user information (claims) from the OpenID Connect provider with ease.
- **Token Refreshing**: Automatically refresh tokens when they expire using the refresh token.
- **Customizable Configurations**: Define multiple OpenID Connect providers in the configuration for multi-provider support.
- **100% Code Coverage and Mutation Testing**: The package is fully tested with 100% code coverage and mutation testing, ensuring high reliability and robustness.

This package is ideal for developers who need robust and secure OpenID Connect functionality within their Laravel applications.

## Installation

1. Install the package via Composer:

```bash
composer require creativecrafts/laravel-openid-connect
```

You can publish the config file with:

```bash
php artisan vendor:publish  --tag="openid-connect-config"
```

This is the contents of the published config file:

```php
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
```
```
EXAMPLE_CLIENT_ID=your-client-id
EXAMPLE_CLIENT_SECRET=your-client-secret
EXAMPLE_REDIRECT_URI=https://your-app.com/callback
```

## Usage

```php
1.Generate Authorization URL:
Redirect users to the provider’s authorization page by generating the URL using the getAuthorizationUrl() method.

use CreativeCrafts\LaravelOpenidConnect\LaravelOpenIdConnect;

$openIdConnect = new LaravelOpenIdConnect('example');
$authorizationUrl = $openIdConnect->getAuthorizationUrl();

return redirect($authorizationUrl);

2.Get Access Token:
After the user is redirected back to your app with an authorization code, you can exchange the code for an access token.

$accessToken = $openIdConnect->getAccessToken($authorizationCode);

3.Get User Info:
Once you have the access token, retrieve user information from the OpenID Connect provider.

$userInfo = $openIdConnect->getUserInfo($accessToken['access_token']);

4.Refresh Token:
If the access token expires, refresh it using the refresh token provided.

$refreshedToken = $openIdConnect->refreshToken($accessToken['refresh_token']);

Error Handling:
If the configuration for the provider is missing or misconfigured, the package throws an InvalidProviderConfigurationException. Ensure that all necessary configurations, such as the issuer URL, client ID, and client secret, are correctly set.
```

## Testing

```bash
This package is built with a focus on quality and reliability. It achieves:

  • 100% Code Coverage: All parts of the package are thoroughly tested to ensure full coverage.
  • Mutation Testing: The package has been tested using mutation testing techniques, which simulate errors to verify that the tests are robust and catch potential issues. This ensures that the package can handle real-world scenarios with high confidence.

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
