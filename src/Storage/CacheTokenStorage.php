<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Storage;

use CreativeCrafts\LaravelOpenidConnect\Contracts\TokenStorageContract;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Psr\SimpleCache\InvalidArgumentException;

final readonly class CacheTokenStorage implements TokenStorageContract
{
    public function __construct(
        private CacheRepository $cache,
        private string $prefix = 'openid_connect_',
        private DateInterval|DateTimeInterface|int|null $ttl = null
    ) {
    }

    /**
     * Store a token value in the cache with the specified key.
     *
     * The key will be prefixed with the configured prefix to avoid cache key conflicts.
     * If no TTL is configured, the value will be stored permanently using the cache's
     * forever method. Otherwise, the value will be stored with the configured TTL.
     *
     * @param string $key The cache key identifier for the token
     * @param string $value The token value to store in the cache
     */
    public function put(string $key, string $value): void
    {
        $cacheKey = $this->prefix . $key;
        if ($this->ttl === null) {
            $this->cache->forever($cacheKey, $value);
            return;
        }
        $this->cache->put($cacheKey, $value, $this->ttl);
    }

    /**
     * Retrieve a token value from the cache using the specified key.
     * The key will be prefixed with the configured prefix to match the storage pattern.
     * The method handles type safety by ensuring only string values are returned, with
     * automatic casting for scalar values and null return for non-scalar or missing values.
     *
     * @param string $key The cache key identifier for the token to retrieve
     * @return string|null The stored token value as a string, or null if not found or invalid type
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?string
    {
        $value = $this->cache->get($this->prefix . $key);
        if (is_string($value)) {
            return $value;
        }
        if ($value === null) {
            return null;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        return null;
    }

    /**
     * Remove a token from the cache using the specified key.
     *
     * The key will be prefixed with the configured prefix to match the storage pattern
     * used by the put and get methods. This ensures consistent key handling across
     * all cache operations.
     *
     * @param string $key The cache key identifier for the token to remove
     */
    public function forget(string $key): void
    {
        $this->cache->forget($this->prefix . $key);
    }

    /**
     * Commit any pending changes to the storage backend.
     *
     * This method provides a consistent interface for storage implementations
     * that may require explicit commit operations (such as database transactions).
     * For cache-based storage, this is a no-operation as cache writes are
     * immediately persisted when put() is called.
     */
    public function commit(): void
    {
        // No-op for cache
    }
}
