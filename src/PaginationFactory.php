<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use LaravelRepository\Rules\RepositoryPagination;
use LaravelRepository\Contracts\PaginationContract;
use LaravelRepository\Contracts\PaginationFormatterContract;

final class PaginationFactory
{
    /**
     * Creates a new pagination object.
     *
     * @param  int $perPage
     * @param  int $page
     * @return PaginationContract
     */
    public static function create(int $perPage, int $page): PaginationContract
    {
        return App::makeWith(PaginationContract::class, [
            'perPage' => $perPage,
            'page' => $page,
        ]);
    }

    /**
     * Crates a new pagination object using parameters passed via request.
     *
     * @param  string $key
     * @param  bool $validate
     * @param  bool $require
     * @return PaginationContract|null
     */
    public static function createFromRequest(
        string $key = 'pagination',
        bool $validate = true,
        bool $require = true
    ): ?PaginationContract {
        $pagination = Request::input($key);

        if (!$require && !$pagination) {
            return null;
        } elseif ($validate) {
            static::validate($pagination, $key, $require);
        }

        return static::createRaw($pagination);
    }

    /**
     * Creates a new instance of this class from a raw pagination string.
     *
     * @param  string $rawStr
     * @return PaginationContract
     * @throws \Exception
     */
    public static function createRaw(string $rawStr): PaginationContract
    {
        $params = App::make(PaginationFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_pagination_string'));
        }

        return static::create($params[1], $params[0]);
    }

    /**
     * Validates pagination params.
     *
     * @param  ?string $data
     * @param  string $key
     * @return void
     * @throws ValidationException
     */
    protected static function validate(
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
}
