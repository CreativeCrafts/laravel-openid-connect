<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelOpenIdConnectServiceProvider extends PackageServiceProvider
{
     // @pest-mutate-ignore
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-openid-connect')
            ->hasConfigFile();
    }
}
