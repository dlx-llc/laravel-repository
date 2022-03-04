<?php

namespace LaravelRepository\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelRepository\Pagination make(int $perPage, int $page)  Creates a new instance of this class.
 * @method static \LaravelRepository\Pagination makeFromRequest(string $key = 'pagination', bool $validate = true, bool $require = true)  Crates a pagination using parameters passed via request.
 * @method static \LaravelRepository\Pagination makeRaw(string $rawStr)  Creates a new instance of this class from a raw pagination string.
 *
 * @see \LaravelRepository\Pagination
 */
class Pagination extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lrepo_pagination';
    }
}
