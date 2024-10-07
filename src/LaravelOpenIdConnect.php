<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use CreativeCrafts\LaravelOpenidConnect\Contracts\LaravelOpenIdConnectContract;
use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AccessTokenData;
use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AuthorizationData;
use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\RefreshTokenData;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\InvalidProviderConfigurationException;
use CreativeCrafts\LaravelOpenidConnect\Services\LaravelOpenIdConnectService;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class LaravelOpenIdConnect implements LaravelOpenIdConnectContract
{
    protected array $providerConfig;

    protected array $authorizationConfig;

    protected array $accessTokenConfig;

    protected array $refreshTokenConfig;

    protected string $issuerUrl;

    /**
     * Constructs a new instance of LaravelOpenIdConnect.
     *
     * @throws InvalidProviderConfigurationException If the provider configuration is not found.
     */
    public function __construct(
        protected string $provider
    ) {
        /** @var array|null $config */
        $config = config("openid-connect.providers.$provider");

        if (is_null($config)) {
            throw new InvalidProviderConfigurationException(
                "Provider configuration not found for: $provider",
                Response::HTTP_NOT_FOUND
            );
        }
        if (isset($config['authorization']['scopes'])) {
            $config['authorization']['scopes'] = implode(' ', $config['authorization']['scopes']);
        }

        $this->providerConfig = $config;
        $this->authorizationConfig = $config['authorization'];
        $this->accessTokenConfig = $config['access_token'];
        $this->refreshTokenConfig = $config['refresh_token'];
        $this->issuerUrl = $config['issuer'];
    }

    /**
     * Generates the authorization URL for the OpenID Connect provider.
     */
    public function getAuthorizationUrl(): string
    {
        $authorizationData = new AuthorizationData($this->authorizationConfig, $this->issuerUrl);
        return $authorizationData->authorizationUrl();
    }

    /**
     * Retrieves the access token from the OpenID Connect provider using the provided authorization code.
     * @throws ConnectionException If there is an issue with the HTTP request to the OpenID Connect provider.
     */
    public function getAccessToken(string $authorizationCode): array
    {
        $accessToken = new AccessTokenData($this->accessTokenConfig, $authorizationCode, $this->issuerUrl);
        $response = LaravelOpenIdConnectService::post(
            $accessToken->url(),
            $accessToken->toArray()
        );

        if ($response->failed()) {
            throw new RuntimeException('Failed to retrieve access token');
        }

        /** @var array $accessToken */
        $accessToken = $response->json();

        if (! is_array($accessToken)) {
            throw new RuntimeException('Response returned null. Check the issuer URL or request format.');
        }

        return $accessToken;
    }

    /**
     * Retrieves user information from the OpenID Connect provider using the provided access token.
     * @throws ConnectionException If there is an issue with the HTTP request to the OpenID Connect provider.
     */
    public function getUserInfo(string $accessToken): array
    {
        $response = LaravelOpenIdConnectService::get("{$this->providerConfig['issuer']}/userinfo", $accessToken);
        /** @var array $userInfo */
        $userInfo = $response->json();
        return $userInfo;
    }

    /**
     * Refreshes the access token using the provided refresh token.
     * @throws ConnectionException If there is an issue with the HTTP request to the OpenID Connect provider.
     */
    public function refreshToken(string $refreshToken): array
    {
        $refreshTokenData = new RefreshTokenData($this->refreshTokenConfig, $refreshToken, $this->issuerUrl);
        $response = LaravelOpenIdConnectService::post(
            $refreshTokenData->url(),
            $refreshTokenData->toArray()
        );

        if ($response->failed()) {
            throw new RuntimeException('Failed to refresh token');
        }

        /** @var array $refreshedToken */
        $refreshedToken = $response->json();

        if (! is_array($refreshedToken)) {
            throw new RuntimeException('Response returned null. Check the issuer URL or request format.');
        }
        return $refreshedToken;
    }
}
