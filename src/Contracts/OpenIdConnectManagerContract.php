<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

/**
 * Interface for managing OpenID Connect operations.
 */
interface OpenIdConnectManagerContract
{
    public function __construct(
        array $config
    );

    /**
     * Authenticates the user using OpenID Connect.
     *
     * @return bool Returns true if the user is successfully authenticated, false otherwise.
     */
    public function authenticate(): bool;
}
