<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Deluxetech\LaRepo\Rules\RepositoryPagination;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\PaginationFormatterContract;

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
            self::validate($pagination, $key, $require);
        }

        return self::createRaw($pagination);
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

        return self::create($params[1], $params[0]);
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
