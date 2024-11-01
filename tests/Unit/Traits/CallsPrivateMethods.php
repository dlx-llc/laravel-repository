<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Unit\Traits;

use ReflectionClass;

trait CallsPrivateMethods
{
    /**
     * Calls private or protected method of the given object.
     *
     * @param array<mixed> $args
     */
    private function callPrivateMethod(object $obj, string $method, array $args = []): mixed
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
