<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\DataTransferObjects;

use CreativeCrafts\LaravelOpenidConnect\Contracts\RefreshTokenDataContract;

/**
 * Represents a Refresh Token data object.
 *
 * This class encapsulates the data required for refreshing access tokens.
 */
final readonly class RefreshTokenData implements RefreshTokenDataContract
{
    /**
     * Constructs a new RefreshTokenData instance.
     *
     * @param array $providerRefreshTokenConfig The configuration for refreshing tokens from the provider.
     * @param string $refreshToken The refresh token obtained from the provider.
     * @param string $issuerUrl The URL of the token issuer.
     */
    public function __construct(
        protected array $providerRefreshTokenConfig,
        protected string $refreshToken,
        protected string $issuerUrl
    ) {
    }

    /**
     * Converts the RefreshTokenData object into an array.
     *
     * This method is used to prepare the data required for refreshing access tokens.
     * It merges the provider's refresh token configuration with the refresh token obtained from the provider.
     *
     * @return array An associative array containing the refresh token configuration and the refresh token.
     */
    public function toArray(): array
    {
        return [
            ...$this->providerRefreshTokenConfig,
            'refresh_token' => $this->refreshToken,
        ];
    }

    /**
     * Returns the URL for refreshing access tokens.
     *
     * This method constructs the URL for refreshing access tokens by appending '/token' to the issuer URL.
     *
     * @return string The URL for refreshing access tokens.
     */
    public function url(): string
    {
        return $this->issuerUrl . '/token';
    }
}
