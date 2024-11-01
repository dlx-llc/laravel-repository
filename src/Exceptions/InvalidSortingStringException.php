<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

class InvalidSortingStringException extends LaRepoException
{
    public function __construct()
    {
        /** @var string $message */
        $message = __('larepo::exceptions.invalid_sorting_string');

        parent::__construct($message);
    }
}
