<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelOpenidConnectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-openid-connect')
            ->hasConfigFile();
    }
}
