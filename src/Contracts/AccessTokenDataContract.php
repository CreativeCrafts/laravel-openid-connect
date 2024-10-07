<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface AccessTokenDataContract
{
    public function __construct(
        array $providerAccessTokenConfig,
        string $authorizationCode,
        string $issuerUrl
    );

    public function toArray(): array;

    public function url(): string;
}
