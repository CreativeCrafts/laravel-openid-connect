<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Services;

use CreativeCrafts\LaravelOpenidConnect\Contracts\OpenIDConnectJWTProcessorContract;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

final class OpenIDConnectJWTProcessor implements OpenIDConnectJWTProcessorContract
{
    /**
     * @var array holds PKCE supported algorithms
     */
    protected array $pkceAlgs = [
        'S256' => 'sha256',
        'plain' => false,
    ];

    private string $clientSecret;

    private array $additionalJwks;

    /**
     * @var string|bool holds code challenge method for PKCE mode
     * @see https://tools.ietf.org/html/rfc7636
     */
    private string|bool $codeChallengeMethod = false;

    /**
     * Constructs the JWT processor.
     *
     * @param string $clientSecret The client secret for JWT signature verification.
     * @param array $additionalJwks Additional JWKs for JWT signature verification.
     */
    public function __construct(string $clientSecret = '', array $additionalJwks = [])
    {
        $this->clientSecret = $clientSecret;
        $this->additionalJwks = $additionalJwks;
    }

    /**
     * Returns the array of supported PKCE algorithms.
     *
     * @return array An associative array where the keys are the algorithm names (e.g., 'S256', 'plain')
     *  and the values are the corresponding hashing algorithms (e.g., 'sha256', false).
     */
    public function pkceSupportedAlgs(): array
    {
        return $this->pkceAlgs;
    }

    /**
     * Returns the code challenge method used for Proof Key for Code Exchange (PKCE) mode.
     *
     * @return bool|string The code challenge method. If PKCE is not used, it returns false.
     *  Otherwise, it returns the code challenge method (e.g., 'S256', 'plain').
     */
    public function getCodeChallengeMethod(): bool|string
    {
        return $this->codeChallengeMethod;
    }

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
    public function setCodeChallengeMethod(string $codeChallengeMethod): void
    {
        $this->codeChallengeMethod = $codeChallengeMethod;
    }

    /**
     * Decodes a JWT into its sections.
     *
     * @param string $jwt The JWT to decode.
     * @param int $section The section of the JWT to return. Default is 0 (header).
     * @return object|null The decoded JWT section or null if the JWT is invalid.
     */
    public function decodeJWT(string $jwt, int $section = 0): ?object
    {
        $parts = explode('.', $jwt);

        /** @var ?object $decodedJWT */
        $decodedJWT = json_decode($this->base64urlDecode($parts[$section] ?? ''), false);
        return $decodedJWT;
    }

    /**
     * Verifies the signature of a JWT using the provided keys.
     *
     * @param string $jwt The JWT to verify.
     * @param array $keys The keys to use for signature verification.
     * @return bool True if the signature is valid, false otherwise.
     * @throws OpenIDConnectClientException
     */
    public function verifyJWTSignature(string $jwt, array $keys): bool
    {
        $parts = explode('.', $jwt);
        if (! isset($parts[0])) {
            throw new OpenIDConnectClientException('Error missing part 0 in token');
        }
        $signature = $this->base64urlDecode(array_pop($parts));
        if ($signature === '' || $signature === '0') {
            throw new OpenIDConnectClientException('Error decoding signature from token');
        }
        $header = json_decode($this->base64urlDecode($parts[0]), false);
        if (! is_object($header)) {
            throw new OpenIDConnectClientException('Error decoding JSON from token header');
        }
        if (! isset($header->alg)) {
            throw new OpenIDConnectClientException('Error missing signature type in token header');
        }

        $payload = implode('.', $parts);
        switch ($header->alg) {
            case 'RS256':
            case 'PS256':
            case 'PS512':
            case 'RS384':
            case 'RS512':
                $hashType = 'sha' . substr($header->alg, 2);
                $signatureType = $header->alg === 'PS256' || $header->alg === 'PS512' ? 'PSS' : '';
                $jwk = $this->getKeyForHeader($keys, $header);
                return $this->verifyRSAJWTSignature($hashType, $jwk, $payload, $signature, $signatureType);
            case 'HS256':
            case 'HS512':
            case 'HS384':
                $hashType = 'SHA' . substr($header->alg, 2);
                return $this->verifyHMACJWTSignature($hashType, $this->clientSecret, $payload, $signature);
            default:
                throw new OpenIDConnectClientException('No support for signature type: ' . $header->alg);
        }
    }

    /**
     * URL-encodes a string according to RFC 3986.
     *
     * @param string $str The string to URL-encode.
     * @return string The URL-encoded string.
     */
    public function urlEncode(string $str): string
    {
        $enc = base64_encode($str);
        $enc = rtrim($enc, '=');
        return strtr($enc, '+/', '-_');
    }

    /**
     * Verifies the RSA signature of a JWT using the provided key.
     *
     * @param string $hashType The hash type used for the signature (e.g., 'sha256').
     * @param object $key The RSA key object containing the public key components.
     * @param string $payload The payload of the JWT.
     * @param string $signature The signature of the JWT.
     * @param string $signatureType The type of signature (e.g., 'PS256', 'PSS', 'PKCS1').
     * @return bool True if the signature is valid, false otherwise.
     * @throws OpenIDConnectClientException If the key object is malformed.
     */
    private function verifyRSAJWTSignature(string $hashType, object $key, string $payload, string $signature, string $signatureType): bool
    {
        if (! (property_exists($key, 'n') && property_exists($key, 'e'))) {
            throw new OpenIDConnectClientException('Malformed key object');
        }
        //@phpstan-ignore-next-line
        $rsaKey = RSA::load([
            'publicExponent' => new BigInteger(base64_decode($this->b64url2b64($key->e)), 256),
            'modulus' => new BigInteger(base64_decode($this->b64url2b64($key->n)), 256),
            'isPublicKey' => true,
        ])->withHash($hashType);

        if ($signatureType === 'PSS') {
            $rsaKey = $rsaKey->withMGFHash($hashType)->withPadding(RSA::SIGNATURE_PSS);
        } else {
            $rsaKey = $rsaKey->withPadding(RSA::SIGNATURE_PKCS1);
        }

        return $rsaKey->verify($payload, $signature);
    }

    /**
     * Verifies the HMAC signature of a JWT using the provided key.
     *
     * @param string $hashType The hash type used for the signature (e.g., 'sha256').
     * @param string $key The secret key used for the HMAC signature.
     * @param string $payload The payload of the JWT.
     * @param string $signature The signature of the JWT.
     * @return bool True if the signature is valid, false otherwise.
     *
     * This function verifies the HMAC signature of a JWT using the provided key and hash type.
     * It calculates the expected signature using the hash_hmac function and compares it with the provided signature.
     * If the signatures match, it returns true; otherwise, it returns false.
     */
    private function verifyHMACJWTSignature(string $hashType, string $key, string $payload, string $signature): bool
    {
        $expected = hash_hmac($hashType, $payload, $key, true);
        return hash_equals($signature, $expected);
    }

    /**
     * Get key for JWT header.
     *
     * This function iterates through the provided keys and additional JWKs to find a matching key for the given JWT header.
     * It checks for a matching 'kid' (key ID) if present in the header, and if not, it looks for a matching 'alg' (algorithm) and 'kid'.
     * If a matching key is found, it is returned. If no matching key is found, an OpenIDConnectClientException is thrown.
     *
     * @param array $keys The array of JWKs (JSON Web Keys) to search for a matching key.
     * @param object $header The JWT header object containing the 'alg' and 'kid' properties.
     * @return object The matching JWK object.
     * @throws OpenIDConnectClientException If no matching key is found.
     */
    private function getKeyForHeader(array $keys, object $header): object
    {
        $allKeys = array_merge($keys, $this->additionalJwks);
        $currentHeader = get_object_vars($header);

        foreach ($allKeys as $currentKey) {
            if ($currentKey['kty'] === 'RSA') {
                if (! isset($currentHeader['kid']) || $currentKey['kid'] === $currentHeader['kid']) {
                    return (object) $currentKey;
                }
            } elseif (isset($currentKey['alg']) && $currentKey['alg'] === $currentHeader['alg'] && $currentKey['kid'] === $currentHeader['kid']) {
                return (object) $currentKey;
            }
        }

        if (isset($currentHeader['kid'])) {
            throw new OpenIDConnectClientException('Unable to find a key for (algorithm, kid):' . $currentHeader['alg'] . ', ' . $currentHeader['kid'] . ')');
        }

        throw new OpenIDConnectClientException('Unable to find a key for RSA');
    }

    /**
     * Decodes a base64 URL encoded string into its original form.
     *
     * This function takes a base64 URL encoded string as input and decodes it using the base64_decode function.
     * It then calls the b64url2b64 function to convert the decoded string from base64 URL format to standard base64 format.
     *
     * @param string $base64url The base64 URL encoded string to decode.
     * @return string The decoded string in standard base64 format.
     */
    private function base64urlDecode(string $base64url): string
    {
        return base64_decode($this->b64url2b64($base64url));
    }

    /**
     * Converts a base64 URL encoded string to a standard base64 string.
     *
     * This function takes a base64 URL encoded string as input and decodes it using the base64_decode function.
     * It then adjusts the padding to ensure the decoded string is a multiple of 4 characters long.
     * Finally, it replaces the URL safe characters '-' and '_' with their standard base64 counterparts '+' and '/' respectively.
     *
     * @param string $base64url The base64 URL encoded string to convert.
     * @return string The converted string in standard base64 format.
     */
    private function b64url2b64(string $base64url): string
    {
        $padding = strlen($base64url) % 4;
        if ($padding > 0) {
            $base64url .= str_repeat('=', 4 - $padding);
        }
        return strtr($base64url, '-_', '+/');
    }
}
