<?php

namespace Deluxetech\LaRepo\Tests\Unit\Traits;

trait CallsPrivateMethods
{
    /**
     * Calls private or protected method of the given object.
     *
     * @param  object $obj
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    private function callPrivateMethod(object $obj, string $method, array $args = []): mixed
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
