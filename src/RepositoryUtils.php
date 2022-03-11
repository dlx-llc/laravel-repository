<?php

namespace Deluxetech\LaRepo;

class RepositoryUtils
{
    /**
     * Checks whether or not the given class is loaded.
     *
     * @param  string $class
     * @return void
     * @throws \Exception
     */
    public static function checkClassExists(string $class): void
    {
        if (!class_exists($class, false)) {
            $msg = __('larepo::exceptions.class_not_defined', compact('class'));

            throw new \Exception($msg);
        }
    }

    /**
     * Checks whether or not the given class is loaded.
     *
     * @param  string $class
     * @param  string $interface
     * @return void
     * @throws \Exception
     */
    public static function checkClassImplements(string $class, string $interface): void
    {
        if (!is_subclass_of($class, $interface)) {
            $msg = __('larepo::exceptions.does_not_implement', compact('class', 'interface'));

            throw new \Exception($msg);
        }
    }
}
