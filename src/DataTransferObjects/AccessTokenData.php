<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\DataTransferObjects;

use CreativeCrafts\LaravelOpenidConnect\Contracts\AccessTokenDataContract;

/**
 * Represents an Access Token Data object.
 *
 * This class is responsible for encapsulating the necessary data for an Access Token request.
 * It provides methods to convert the data into an array and retrieve the token endpoint URL.
 */

final readonly class AccessTokenData implements AccessTokenDataContract
{
    /**
     * Constructs a new AccessTokenData instance.
     *
     * @param array $providerAccessTokenConfig The configuration for the access token request.
     * @param string $authorizationCode The authorization code obtained from the authorization flow.
     * @param string $issuerUrl The URL of the OpenID Connect provider's issuer.
     */
    public function __construct(
        protected array $providerAccessTokenConfig,
        protected string $authorizationCode,
        protected string $issuerUrl
    ) {
    }

    /**
     * Converts the Access Token Data object into an array.
     *
     * This method is used to prepare the data for the access token request. It merges the
     * provider's access token configuration with the authorization code obtained from the
     * authorization flow.
     */
    public function toArray(): array
    {
        return [
            ...$this->providerAccessTokenConfig,
            'code' => $this->authorizationCode,
        ];
    }

    /**
     * Returns the URL for the access token request.
     *
     * This method constructs the URL for the access token request by appending '/token' to the issuer URL.
     */
    public function url(): string
    {
        return $this->issuerUrl . '/token';
    }
}
