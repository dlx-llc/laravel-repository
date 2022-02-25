<?php

namespace LaravelRepository;

/**
 * Example: John,[first_name,middle_name,last_name]
 */
class TextSearch
{
    /**
     * @var array<string>
     */
    public array $attrs = [];

    /**
     * Makes a new instance of this class.
     *
     * @param  string $text
     * @param  string ...$attrs
     * @return static
     */
    public static function make(string $text, string ...$attrs): static
    {
        return new static($text, ...$attrs);
    }

    /**
     * Constructor.
     *
     * @param  string $text
     * @param  string ...$attrs
     * @return void
     */
    public function __construct(public string $text, string ...$attrs)
    {
        $this->attrs = $attrs;
    }
}
