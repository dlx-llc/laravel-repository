<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use LaravelRepository\Rules\RepositoryPagination;

class Pagination
{
    /**
     * Creates a new instance of this class.
     *
     * @param  int $perPage
     * @param  int $page
     * @return static
     */
    public static function make(int $perPage, int $page): static
    {
        return new static($perPage, $page);
    }

    /**
     * Crates a pagination using parameters passed by the request.
     *
     * @param  string $key
     * @param  bool $validate
     * @param  bool $require
     * @return static|null
     */
    public static function makeFromRequest(
        string $key = 'pagination',
        bool $validate = true,
        bool $require = true
    ): ?static {
        $pagination = Request::input($key);

        if (!$require && !$pagination) {
            return null;
        } elseif ($validate) {
            static::validate($pagination, $key, $require);
        }

        return static::makeRaw($pagination);
    }

    /**
     * Creates a new instance of this class from a raw pagination string.
     *
     * @param  string $rawStr
     * @return static
     * @throws \Exception
     */
    public static function makeRaw(string $rawStr): static
    {
        $params = static::parseStr($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_pagination_string'));
        }

        return static::make($params[1], $params[0]);
    }

    /**
     * Parses pagination raw string params.
     *
     * @param  string $rawStr
     * @return array|null
     */
    public static function parseStr(string $rawStr): ?array
    {
        if (!preg_match('/^([1-9]\d*)\,([1-9]\d*)$/', $rawStr, $matches)) {
            return null;
        }

        $page = intval($matches[1]);
        $perPage = intval($matches[2]);

        return [$page, $perPage];
    }

    /**
     * Validates pagination params.
     *
     * @param  ?string $data
     * @param  string $key
     * @return void
     * @throws ValidationException
     */
    public static function validate(
        ?string $data,
        string $key = 'pagination',
        bool $require = true
    ): void {
        if (!$require && is_null($data)) {
            return;
        }

        $rules = $require ? ['required'] : [];
        $rules[] = new RepositoryPagination();
        Validator::make([$key => $data], [$key => $rules])->validate();
    }

    /**
     * Constructor.
     *
     * @param  int $perPage
     * @param  int $page
     * @return void
     */
    public function __construct(
        public int $perPage,
        public int $page
    ) {
        //
    }
}
