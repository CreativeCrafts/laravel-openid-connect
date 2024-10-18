<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Helpers;

use CreativeCrafts\LaravelOpenidConnect\Contracts\Base64HelperContract;

final class Base64Helper implements Base64HelperContract
{
    /**
     * Decodes a base64url encoded string to its original form.
     *
     * @param string $base64url The base64url encoded string to decode.
     * @return bool|string Returns the decoded string on success, or false on failure.
     */
    public static function base64urlDecode(string $base64url): bool|string
    {
        return base64_decode(self::b64url2b64($base64url));
    }

    /**
     * Converts a base64url encoded string to a standard base64 encoded string.
     *
     * @param string $base64url The base64url encoded string to convert.
     * @return string Returns the converted base64 encoded string.
     */
    public static function b64url2b64(string $base64url): string
    {
        $padding = strlen($base64url) % 4;
        if ($padding > 0) {
            $base64url .= str_repeat('=', 4 - $padding);
        }
        return strtr($base64url, '-_', '+/');
    }

    /**
     * Encodes a string to base64url format.
     *
     * This function takes a string as input and encodes it to base64url format.
     * Base64url encoding is a variation of base64 encoding that replaces '+' with '-', '/' with '_',
     * and removes trailing '=' characters.
     *
     * @param string $data The string to be encoded.
     * @return string Returns the encoded string in base64url format.
     */
    public static function b64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
