<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
pest()->project()->github('CreativeCrafts/laravel-openid-connect');


/**
 * Helper function to access private properties in a class.
 *
 * @param object $object
 * @param string $propertyName
 * @return mixed
 */
function getPrivateProperty($object, string $propertyName): mixed
{
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    $property->setAccessible(true);
    return $property->getValue($object);
}
