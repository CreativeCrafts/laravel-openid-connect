<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Helpers\Base64Helper;

it('encodes and decodes base64url correctly', function () {
    $data = random_bytes(32);

    $encoded = Base64Helper::b64urlEncode($data);

    expect($encoded)
        ->not()->toContain('=')
        ->and($encoded)->toMatch('/^[A-Za-z0-9\-_]+$/');

    $decoded = Base64Helper::base64urlDecode($encoded);
    expect($decoded)->toBe($data);
});

it('converts base64url to standard base64 with correct padding restoration', function () {
    $standard = base64_encode('foo'); // Zm9v
    $url = rtrim(strtr($standard, '+/', '-_'), '=');

    $converted = Base64Helper::b64url2b64($url);

    expect($converted)->toBe($standard);
});
