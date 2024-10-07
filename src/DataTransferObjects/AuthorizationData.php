<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\DataTransferObjects;

use CreativeCrafts\LaravelOpenidConnect\Contracts\AuthorizationDataContract;
use InvalidArgumentException;

/**
 * Represents authorization data for the OpenID Connect flow.
 */
final readonly class AuthorizationData implements AuthorizationDataContract
{
    /**
     * Constructs a new instance of AuthorizationData.
     *
     * @param array $providerAuthorisationConfig The configuration parameters for the authorization process.
     * @param string $issuerUrl The URL of the OpenID Connect provider's issuer.
     */
    public function __construct(
        protected array $providerAuthorisationConfig,
        protected string $issuerUrl
    ) {
    }

    /**
     * Returns the query parameters required for the authorization code grant flow.
     *
     * @throws InvalidArgumentException If any of the required parameters (redirect_uri, response_type, scope, state) are not provided.
     *
     * @return string The query parameters as a URL-encoded string.
     */
    public function queryParameters(): string
    {
        return http_build_query($this->providerAuthorisationConfig);
    }

    /**
     * Returns the full authorization URL for the authorization code grant flow.
     *
     * @return string The full authorization URL.
     */
    public function authorizationUrl(): string
    {
        return $this->issuerUrl . '/authorize?' . $this->queryParameters();
    }
}
