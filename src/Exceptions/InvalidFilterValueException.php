<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

use Throwable;

class InvalidFilterValueException extends LaRepoException
{
    public function __construct(string $attribute, ?Throwable $previous = null)
    {
        parent::__construct(
            message: __('larepo::exceptions.invalid_filter_value_for_attr', [
                'attr' => $attribute,
            ]),
            previous: $previous,
        );
    }
}
