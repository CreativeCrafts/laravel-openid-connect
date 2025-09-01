<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface TokenStorageContract
{
    public function put(string $key, string $value): void;

    public function get(string $key): ?string;

    public function forget(string $key): void;

    public function commit(): void;
}
