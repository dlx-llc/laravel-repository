<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

class UnsupportedFilterOperatorException extends LaRepoException
{
    public function __construct(string $operator)
    {
        /** @var string $message */
        $message = __('larepo::exceptions.unsupported_filter_operator', [
            'operator' => $operator,
        ]);

        parent::__construct($message);
    }
}
