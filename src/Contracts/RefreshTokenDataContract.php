<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface RefreshTokenDataContract
{
    public function __construct(
        array $providerRefreshTokenConfig,
        string $refreshToken,
        string $issuerUrl
    );

    public function toArray(): array;

    public function url(): string;
}
