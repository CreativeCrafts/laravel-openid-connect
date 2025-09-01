<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;

beforeEach(function () {
    $this->tm = new OpenIDConnectTokenManager();
});

it('saves and loads a state bundle with nonce and code_verifier', function () {
    $state = 'state_' . bin2hex(random_bytes(4));
    $nonce = 'nonce_' . bin2hex(random_bytes(4));
    $codeVerifier = 'cv_' . bin2hex(random_bytes(4));

    $this->tm->saveStateBundle($state, $nonce, $codeVerifier);

    $bundle = $this->tm->loadStateBundle($state);

    expect($bundle)
        ->toBeArray()
        ->and($bundle['nonce'])->toBe($nonce)
        ->and($bundle['code_verifier'])->toBe($codeVerifier);
});

it('saves and loads a state bundle without code_verifier', function () {
    $state = 'state_' . bin2hex(random_bytes(4));
    $nonce = 'nonce_' . bin2hex(random_bytes(4));

    $this->tm->saveStateBundle($state, $nonce, null);

    $bundle = $this->tm->loadStateBundle($state);

    expect($bundle)
        ->toBeArray()
        ->and($bundle['nonce'])->toBe($nonce)
        ->and($bundle['code_verifier'])->toBeNull();
});

it('returns null when loading a non-existent state bundle', function () {
    $missing = $this->tm->loadStateBundle('does_not_exist_' . bin2hex(random_bytes(4)));
    expect($missing)->toBeNull();
});

it('clears a saved state bundle', function () {
    $state = 'state_' . bin2hex(random_bytes(4));
    $nonce = 'nonce_' . bin2hex(random_bytes(4));

    $this->tm->saveStateBundle($state, $nonce, null);
    expect($this->tm->loadStateBundle($state))->not()->toBeNull();

    $this->tm->clearStateBundle($state);
    expect($this->tm->loadStateBundle($state))->toBeNull();
});
