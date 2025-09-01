<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;
use Illuminate\Support\Carbon;

it('expires cache-stored state bundles and yields unable to determine state', function () {
    // Configure cache storage with very small TTL
    config()->set('openid-connect.storage', 'cache');
    config()->set('openid-connect.cache_store', 'array');
    config()->set('openid-connect.cache_ttl', 1); // seconds

    $tm = new OpenIDConnectTokenManager();

    $state = 'expiring';
    $nonce = 'n';
    $tm->saveStateBundle($state, $nonce, null);

    // Advance time beyond TTL
    Carbon::setTestNow(Carbon::now()->addSeconds(2));

    expect($tm->loadStateBundle($state))->toBeNull();

    // Reset time travel
    Carbon::setTestNow();
});
