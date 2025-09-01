<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Storage;

use CreativeCrafts\LaravelOpenidConnect\Contracts\TokenStorageContract;

final class NullTokenStorage implements TokenStorageContract
{
    /**
     * Store a token value with the specified key.
     *
     * This is a no-operation implementation that does not actually store anything.
     * Used when token storage is disabled or not required.
     *
     * @param string $key The unique identifier for the token
     * @param string $value The token value to store
     */
    public function put(string $key, string $value): void
    {
        // no-op
    }

    /**
     * Retrieve a token value by its key.
     *
     * This is a no-operation implementation that always returns null,
     * as this storage implementation does not actually store any tokens.
     *
     * @param string $key The unique identifier for the token to retrieve
     * @return string|null Always returns null since no tokens are stored
     */
    public function get(string $key): ?string
    {
        return null;
    }

    /**
     * Remove a token from storage by its key.
     *
     * This is a no-operation implementation that does not actually remove anything,
     * as this storage implementation does not store any tokens to begin with.
     *
     * @param string $key The unique identifier for the token to remove
     */
    public function forget(string $key): void
    {
        // no-op
    }

    /**
     * Commit any pending changes to the storage.
     *
     * This is a no-operation implementation that does not perform any commit operations,
     * as this storage implementation does not actually store or modify any data.
     */
    public function commit(): void
    {
        // no-op
    }
}
