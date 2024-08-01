<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Exceptions;

class InvalidFilterValueException extends LaRepoException
{
    public function __construct(string $attribute)
    {
        parent::__construct(
            __('larepo::exceptions.invalid_filter_value_for_attr', [
                'attr' => $attribute,
            ]),
        );
    }
}
