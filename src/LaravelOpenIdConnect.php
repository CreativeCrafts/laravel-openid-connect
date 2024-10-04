<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use CreativeCrafts\LaravelOpenidConnect\Contracts\LaravelOpenIdConnectContract;
use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AuthorizationData;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\InvalidProviderConfigurationException;
use Illuminate\Http\Client\ConnectionException;
use Symfony\Component\HttpFoundation\Response;

final class LaravelOpenIdConnect implements LaravelOpenIdConnectContract
{
    protected array $providerConfig;

    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected string $scopes;

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

        $this->providerConfig = $config;

        $this->clientId = $this->providerConfig['client_id'];
        $this->clientSecret = $this->providerConfig['client_secret'];
        $this->redirectUri = $this->providerConfig['redirect_uri'];
        $this->scopes = implode(' ', $this->providerConfig['scopes']);
    }

    /**
     * Generates the authorization URL for the OpenID Connect provider.
     */
    public function getAuthorizationUrl(): string
    {
        $authorizationData = new AuthorizationData(
            clientId: $this->clientId,
            redirectUri: $this->redirectUri,
            responseType: 'code',
            scope: $this->scopes,
            state: csrf_token()
        );

        $queryParams = $authorizationData->queryParameters();
        return "{$this->providerConfig['issuer']}/authorize?$queryParams";
    }

    /**
     * Retrieves the access token from the OpenID Connect provider using the provided authorization code.
     * @throws ConnectionException If there is an issue with the HTTP request to the OpenID Connect provider.
     */
    public function getAccessToken(string $authorizationCode): array
    {
        $authorizationData = new AuthorizationData(
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            url: "{$this->providerConfig['issuer']}/token",
            redirectUri: $this->redirectUri,
            code: $authorizationCode
        );
        $response = LaravelOpenIdConnectService::post(
            $authorizationData->issuerUrl(),
            $authorizationData->accessTokenData()
        );

        /** @var array $accessToken */
        $accessToken = $response->json();

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
        $authorizationData = new AuthorizationData(
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            url: "{$this->providerConfig['issuer']}/token",
            refreshToken: $refreshToken
        );
        $response = LaravelOpenIdConnectService::post(
            $authorizationData->issuerUrl(),
            $authorizationData->refreshTokenData()
        );

        /** @var array $refreshedToken */
        $refreshedToken = $response->json();
        return $refreshedToken;
    }
}
