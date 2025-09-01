<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Storage;

use CreativeCrafts\LaravelOpenidConnect\Contracts\TokenStorageContract;
use Illuminate\Contracts\Session\Session as SessionContract;
use InvalidArgumentException;

final class SessionTokenStorage implements TokenStorageContract
{
    private SessionContract $session;
    private string $prefix;

    public function __construct(
        object $session,
        string $prefix = 'openid_connect_'
    ) {
        // Normalise: if a SessionManager/Factory is provided, get the underlying driver/store
        if (method_exists($session, 'driver')) {
            $driver = $session->driver();
            if ($driver instanceof SessionContract) {
                $session = $driver;
            }
        }

        if (!$session instanceof SessionContract) {
            throw new InvalidArgumentException('Session must implement ' . SessionContract::class);
        }

        $this->session = $session;
        $this->prefix = $prefix;
    }

    /**
     * Store a value in the session with the configured prefix.
     *
     * @param string $key The key to store the value under (will be prefixed)
     * @param string $value The value to store in the session
     */
    public function put(string $key, string $value): void
    {
        $this->session->put($this->prefix . $key, $value);
    }

    /**
     * Retrieve a value from the session using the configured prefix.
     *
     * This method fetches a value from the session storage using the provided key
     * with the configured prefix prepended. It handles type conversion to ensure
     * a string return value, converting scalar values to strings when possible.
     *
     * @param string $key The key to retrieve the value for (will be prefixed)
     * @return string|null The stored value as a string, or null if not found or not convertible
     */
    public function get(string $key): ?string
    {
        $value = $this->session->get($this->prefix . $key);
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
     * Remove a value from the session using the configured prefix.
     *
     * This method removes a stored value from the session storage by deleting
     * the entry associated with the provided key (with the configured prefix
     * prepended). Once forgotten, the value will no longer be retrievable.
     *
     * @param string $key The key to remove from the session (will be prefixed)
     */
    public function forget(string $key): void
    {
        $this->session->forget($this->prefix . $key);
    }

    /**
     * Commit and persist all session changes to storage.
     *
     * This method ensures that any pending session data modifications are
     * written to the underlying storage mechanism. It checks if the session
     * implementation supports the save() method and calls it to persist
     * the current session state.
     */
    public function commit(): void
    {
        // Ensure the session is saved if supported
        // @phpstan-ignore-next-line Laravel session contracts have save()
        if (method_exists($this->session, 'save')) {
            $this->session->save();
        }
    }
}
