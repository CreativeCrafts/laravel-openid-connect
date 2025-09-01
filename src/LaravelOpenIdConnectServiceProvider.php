<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use CreativeCrafts\LaravelOpenidConnect\Contracts\TokenStorageContract;
use CreativeCrafts\LaravelOpenidConnect\Storage\CacheTokenStorage;
use CreativeCrafts\LaravelOpenidConnect\Storage\NullTokenStorage;
use CreativeCrafts\LaravelOpenidConnect\Storage\SessionTokenStorage;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelOpenIdConnectServiceProvider extends PackageServiceProvider
{
    // @pest-mutate-ignore
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-openid-connect')
            ->hasConfigFile();
    }

    /**
     * Register package services and bindings after the package has been registered.
     * This method binds the TokenStorageContract to the appropriate storage implementation
     * based on the configuration settings. It supports three storage drivers: cache, session,
     * and none (null storage). The binding is configured to resolve the correct storage
     * implementation when the TokenStorageContract is requested from the service container.
     */
    public function packageRegistered(): void
    {
        // Bind token storage according to configuration for consumers who resolve it directly
        $this->app->bind(TokenStorageContract::class, function ($app) {
            $driver = Config::string(key: 'openid-connect.storage', default: 'session');
            $prefix = Config::string(key: 'openid-connect.session_key_prefix', default: 'openid_connect_');
            if ($driver === 'cache') {
                $store = Config::string(key: 'openid-connect.cache_store', default: '');
                /** @var Factory $cacheFactory */
                $cacheFactory = $app['cache'];
                $repo = $store !== '' && $store !== '0' ? $cacheFactory->store($store) : $app['cache.store'];
                $ttl = Config::integer(key: 'openid-connect.cache_ttl', default: 300);
                return new CacheTokenStorage($repo, $prefix, $ttl);
            }
            if ($driver === 'none') {
                return new NullTokenStorage();
            }
            // default to session
            return new SessionTokenStorage($app['session'], $prefix);
        });
    }
}
