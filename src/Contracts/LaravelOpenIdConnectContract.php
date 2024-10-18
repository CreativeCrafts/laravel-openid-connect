<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenIdConnect\Contracts;

/**
 * Interface for Laravel OpenID Connect package.
 *
 * This interface provides methods for interacting with the OpenID Connect provider.
 */
interface LaravelOpenIdConnectContract
{
    /**
     * Accepts a specific OpenID Connect provider.
     *
     * @param string|null $provider The name of the OpenID Connect provider. If null, the default provider will be used.
     * @return self Returns the instance of the class for method chaining.
     */
    public function acceptProvider(?string $provider = null): self;

    /**
     * Authenticates the user with the OpenID Connect provider.
     *
     * @return bool Returns true if the user is successfully authenticated, false otherwise.
     */
    public function authenticate(): bool;

    /**
     * Retrieves user information from the OpenID Connect provider.
     *
     * @param string|null $attribute The specific attribute to retrieve. If null, all user information will be returned.
     * @param bool|null $addOpenIdSchema If true, the OpenID Connect schema will be added to the attribute.
     * @return mixed Returns the user information as an associative array or a specific attribute value.
     */
    public function retrieveUserInfo(?string $attribute = null, ?bool $addOpenIdSchema = false): mixed;
}
