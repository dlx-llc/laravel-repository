<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use UnexpectedValueException;

class ClassUtils
{
    /**
     * Checks whether or not the given class is loaded.
     *
     * @throws UnexpectedValueException
     */
    public static function checkClassExists(string $class): void
    {
        if (!class_exists($class, true)) {
            /** @var string $message */
            $message = __('larepo::exceptions.class_not_defined', compact('class'));

            throw new UnexpectedValueException($message);
        }
    }

    /**
     * Checks whether or not the given class is loaded.
     *
     * @throws UnexpectedValueException
     */
    public static function checkClassImplements(string $class, string $interface): void
    {
        if (!is_subclass_of($class, $interface)) {
            /** @var string $message */
            $message = __('larepo::exceptions.does_not_implement', compact('class', 'interface'));

            throw new UnexpectedValueException($message);
        }
    }
}
