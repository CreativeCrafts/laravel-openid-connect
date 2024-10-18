<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

/**
 * Interface for processing OpenID Connect JWTs.
 */
interface OpenIDConnectJWTProcessorContract
{
    /**
     * Constructs the JWT processor.
     *
     * @param string $clientSecret The client secret for JWT signature verification.
     * @param array $additionalJwks Additional JWKs for JWT signature verification.
     */
    public function __construct(string $clientSecret, array $additionalJwks = []);

    /**
     * Returns the array of supported PKCE algorithms.
     *
     * @return array An associative array where the keys are the algorithm names (e.g., 'S256', 'plain')
     *  and the values are the corresponding hashing algorithms (e.g., 'sha256', false).
     */
    public function pkceSupportedAlgs(): array;
    /**
     * Decodes a JWT into its sections.
     *
     * @return object|null The decoded JWT section or null if the JWT is invalid.
     */

    /**
     * Returns the code challenge method used for Proof Key for Code Exchange (PKCE) mode.
     *
     * @return bool|string The code challenge method. If PKCE is not used, it returns false.
     *  Otherwise, it returns the code challenge method (e.g., 'S256', 'plain').
     */
    public function getCodeChallengeMethod(): bool|string;

    /**
     * Sets the code challenge method used for Proof Key for Code Exchange (PKCE) mode.
     *
     * This method allows you to specify the code challenge method to be used when initiating
     * a PKCE flow. The supported code challenge methods are defined in the $pkceAlgs property.
     *
     * @param string $codeChallengeMethod The code challenge method. It should be one of the keys
     *  defined in the $pkceAlgs property (e.g., 'S256', 'plain'). If PKCE is not used,
     *  this method should be called with a boolean false value.
     */
    public function setCodeChallengeMethod(string $codeChallengeMethod): void;

    /**
     * Decodes a JWT into its sections.
     *
     * @param string $jwt The JWT to decode.
     * @param int $section The section of the JWT to return. Default is 0 (header).
     * @return object|null The decoded JWT section or null if the JWT is invalid.
     */
    public function decodeJWT(string $jwt, int $section = 0): ?object;

    /**
     * Verifies the signature of a JWT using the provided keys.
     *
     * @param string $jwt The JWT to verify.
     * @param array $keys The keys to use for signature verification.
     * @return bool True if the signature is valid, false otherwise.
     */
    public function verifyJWTSignature(string $jwt, array $keys): bool;

    /**
     * URL-encodes a string according to RFC 3986.
     *
     * @param string $str The string to URL-encode.
     * @return string The URL-encoded string.
     */
    public function urlEncode(string $str): string;
}
