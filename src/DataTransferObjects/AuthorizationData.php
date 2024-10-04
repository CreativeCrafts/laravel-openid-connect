<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\DataTransferObjects;

use CreativeCrafts\LaravelOpenidConnect\Contracts\AuthorizationDataContract;
use InvalidArgumentException;

final readonly class AuthorizationData implements AuthorizationDataContract
{
    public function __construct(
        protected string $clientId,
        protected ?string $clientSecret = null,
        protected string $url = '',
        protected ?string $redirectUri = null,
        protected ?string $code = null,
        protected ?string $refreshToken = null,
        protected ?string $responseType = null,
        protected ?string $scope = null,
        protected ?string $state = null
    ) {
    }

    /**
     * Returns the data required for the authorization code grant flow to obtain an access token.
     *
     * @return array An associative array containing the following keys:
     *  - grant_type: The grant type, which should be 'authorization_code'.
     *  - client_id: The client identifier.
     *  - client_secret: The client secret.
     *  - redirect_uri: The redirect URI provided during the authorization request.
     *  - code: The authorization code obtained during the authorization code grant flow.
     *
     * @throws InvalidArgumentException If the client secret, redirect URI, or authorization code is not provided.
     */
    public function accessTokenData(): array
    {
        if ($this->clientSecret === null) {
            throw new InvalidArgumentException('Client secret is required for refresh token grant.');
        }

        if ($this->redirectUri === null) {
            throw new InvalidArgumentException('Redirect URI is required for authorization code grant.');
        }

        if ($this->code === null) {
            throw new InvalidArgumentException('Authorization code is required for authorization code grant.');
        }

        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $this->code,
        ];
    }

    /**
     * Returns the issuer URL for the OpenID Connect provider.
     *
     * @throws InvalidArgumentException If the issuer URL is not provided.
     */
    public function issuerUrl(): string
    {
        if ($this->url === '') {
            throw new InvalidArgumentException('Issuer URL is required.');
        }
        return $this->url;
    }

    /**
     * Returns an array of parameters required for the refresh token grant flow to obtain a new access token.
     *
     * @return array An associative array containing the following keys:
     *  - grant_type: The grant type, which should be 'refresh_token'.
     *  - client_id: The client identifier.
     *  - client_secret: The client secret.
     *  - refresh_token: The refresh token obtained during the authorization code grant flow.
     *
     * @throws InvalidArgumentException If the client secret is not provided.
     */
    public function refreshTokenData(): array
    {
        if ($this->clientSecret === null) {
            throw new InvalidArgumentException('Client secret is required for refresh token grant.');
        }

        return [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
        ];
    }

    /**
     * Returns the query parameters required for the authorization code grant flow.
     *
     * @throws InvalidArgumentException If any of the required parameters (redirect_uri, response_type, scope, state) are not provided.
     */
    public function queryParameters(): string
    {
        if ($this->redirectUri === null) {
            throw new InvalidArgumentException('Redirect URI is required for authorization code grant.');
        }

        if ($this->responseType === null) {
            throw new InvalidArgumentException('Response type is required for authorization code grant.');
        }

        if ($this->scope === null) {
            throw new InvalidArgumentException('Scope is required for authorization code grant.');
        }

        if ($this->state === null) {
            throw new InvalidArgumentException('State is required for authorization code grant.');
        }
        return http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => $this->responseType,
            'scope' => $this->scope,
            'state' => $this->state,
        ]);
    }
}
