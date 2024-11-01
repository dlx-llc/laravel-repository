<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

class InvalidFiltersStringException extends LaRepoException
{
    public function __construct()
    {
        /** @var string $message */
        $message = __('larepo::exceptions.invalid_filters_string');

        parent::__construct($message);
    }
}
