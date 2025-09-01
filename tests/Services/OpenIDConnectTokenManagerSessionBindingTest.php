<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;

beforeEach(function () {
    // Ensure default config/session are available via Testbench
    $this->tm = new OpenIDConnectTokenManager();
});

it('binds state bundle to current session via sid and rejects mismatched sid', function () {
    $state = 'state_' . bin2hex(random_bytes(4));
    $nonce = 'nonce_' . bin2hex(random_bytes(4));
    $codeVerifier = 'cv_' . bin2hex(random_bytes(4));

    // Save with current session (sid A)
    $this->tm->saveStateBundle($state, $nonce, $codeVerifier);

    // Tamper the stored bundle to simulate a different sid (sid B)
    $key = 'openid_connect_oidc:state:' . $state; // matches SessionTokenStorage prefix + key
    $raw = session()->get($key);
    expect($raw)->toBeString();
    $data = json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
    $data['sid'] = 'mismatched_sid_value';
    session()->put($key, json_encode($data, JSON_THROW_ON_ERROR));

    // Now loading should return null due to sid mismatch
    $bundle = $this->tm->loadStateBundle($state);
    expect($bundle)->toBeNull();
});

it('supports multiple concurrent state bundles within the same session', function () {
    $state1 = 's1_' . bin2hex(random_bytes(3));
    $nonce1 = 'n1_' . bin2hex(random_bytes(3));
    $cv1 = 'cv1_' . bin2hex(random_bytes(3));

    $state2 = 's2_' . bin2hex(random_bytes(3));
    $nonce2 = 'n2_' . bin2hex(random_bytes(3));
    $cv2 = null; // no PKCE in second flow

    $this->tm->saveStateBundle($state1, $nonce1, $cv1);
    $this->tm->saveStateBundle($state2, $nonce2, $cv2);

    $b1 = $this->tm->loadStateBundle($state1);
    $b2 = $this->tm->loadStateBundle($state2);

    expect($b1)->toBeArray()
        ->and($b1['nonce'])->toBe($nonce1)
        ->and($b1['code_verifier'])->toBe($cv1)
        ->and($b2)->toBeArray()
        ->and($b2['nonce'])->toBe($nonce2)
        ->and($b2['code_verifier'])->toBeNull();
});
