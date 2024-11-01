<?php

declare(strict_types=1);

return [
    'array' => 'The :attribute must be an array.',
    'boolean' => 'The :attribute field must be true or false.',
    'required' => 'The :attribute field is required.',
    'starts_with' => 'The :attribute must start with one of the following: :values',
    'string' => 'The :attribute must be a string.',
    'json' => 'The :attribute must be a valid JSON string.',
    'in' => 'The selected :attribute is invalid.',
    'between' => [
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
    ],
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'size' => [
        'array' => 'The :attribute must contain :size items.',
    ],

    'absent' => 'The :attribute field must be absent.',
    'scalar' => 'The :attribute must be a scalar value.',
    'array_or_scalar' => 'The :attribute must be an array or a scalar value.',
    'repository_sort' => 'The :attribute must confirm the following format: "field,dir", where field is a valid field name and the dir is either "asc" or "desc".',
    'repository_search' => 'The :attribute must confirm the following format: "text,[field1,field2,...]", where each field is a valid field name and the search text is not empty.',
    'repository_filters' => 'The :attribute must be a valid filters array.',
];
