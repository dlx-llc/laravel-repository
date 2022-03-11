<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Deluxetech\LaRepo\Contracts\PaginationContract;

final class PaginationFactory
{
    /**
     * Creates a new pagination object.
     *
     * @param  int $page
     * @param  int $perPage
     * @return PaginationContract
     */
    public static function create(int $page, int $perPage): PaginationContract
    {
        return App::makeWith(PaginationContract::class, [
            'perPage' => $perPage,
            'page' => $page,
        ]);
    }

    /**
     * Crates a new pagination object using parameters passed via request.
     *
     * @param  string $pageKey
     * @param  string $perPageKey
     * @param  bool $validate
     * @param  bool $require
     * @return PaginationContract|null
     */
    public static function createFromRequest(
        string $pageKey = 'page',
        string $perPageKey = 'perPage',
        bool $validate = true,
        bool $require = true
    ): ?PaginationContract {
        $page = Request::input('page');
        $perPage = Request::input('perPage', 15);

        if (!$require && !$page) {
            return null;
        } elseif ($validate) {
            self::validate($pageKey, $perPageKey, $page, $perPage, $require);
        }

        return self::create($perPage, $page);
    }

    /**
     * Validates pagination params.
     *
     * @param  string $pageKey
     * @param  string $perPageKey
     * @param  ?int $page
     * @param  int $perPage
     * @return void
     * @throws ValidationException
     */
    protected static function validate(
        string $pageKey,
        string $perPageKey,
        ?int $page,
        int $perPage,
        bool $require = true
    ): void {
        if (!$require && is_null($page)) {
            return;
        }

        $pageRules = ['integer', 'min:1'];
        $perPageRules = ['integer', 'min:1', 'max:1000'];

        if ($require) {
            $pageRules[] = ['required'];
        }

        Validator::make([
            $pageKey => $page,
            $perPageKey => $perPage,
        ], [
            $pageKey => $pageRules,
            $perPageKey => $perPageRules,
        ])->validate();
    }
}
