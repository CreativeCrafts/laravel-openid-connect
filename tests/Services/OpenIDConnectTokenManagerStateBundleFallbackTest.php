<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;

beforeEach(function () {
    $this->tm = new OpenIDConnectTokenManager();
});

it('loads fallback bundle from legacy session keys when scoped bundle is missing', function () {
    $state = 'state_' . bin2hex(random_bytes(4));
    $nonce = 'nonce_' . bin2hex(random_bytes(4));
    $codeVerifier = 'cv_' . bin2hex(random_bytes(4));

    // simulate legacy behavior: store state/nonce/code_verifier without saving a state bundle
    $this->tm->setState($state);
    $this->tm->setNonce($nonce);
    $this->tm->setCodeVerifier($codeVerifier);

    $bundle = $this->tm->loadStateBundle($state);

    expect($bundle)
        ->toBeArray()
        ->and($bundle['nonce'])->toBe($nonce)
        ->and($bundle['code_verifier'])->toBe($codeVerifier);
});
