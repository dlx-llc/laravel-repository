<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

class InvalidTextSearchStringException extends LaRepoException
{
    public function __construct()
    {
        /** @var string $message */
        $message = __('larepo::exceptions.invalid_text_search_string');

        parent::__construct($message);
    }
}
