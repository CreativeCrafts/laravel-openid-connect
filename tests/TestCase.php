<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Tests;

use CreativeCrafts\LaravelOpenidConnect\LaravelOpenIdConnectServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CreativeCrafts\\LaravelOpenIdConnectbak\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-openid-connect_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelOpenIdConnectServiceProvider::class,
        ];
    }
}
