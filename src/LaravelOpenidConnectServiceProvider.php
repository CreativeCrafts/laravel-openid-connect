<?php

namespace CreativeCrafts\LaravelOpenidConnect;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CreativeCrafts\LaravelOpenidConnect\Commands\LaravelOpenidConnectCommand;

class LaravelOpenidConnectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-openid-connect')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_openid_connect_table')
            ->hasCommand(LaravelOpenidConnectCommand::class);
    }
}
