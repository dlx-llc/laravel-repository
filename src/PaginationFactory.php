<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
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
     * @param  bool $validate
     * @param  bool $require
     * @param  string|null $pageKey
     * @param  string|null $perPageKey
     * @return PaginationContract|null
     */
    public static function createFromRequest(
        bool $validate = true,
        bool $require = true,
        ?string $pageKey = null,
        ?string $perPageKey = null
    ): ?PaginationContract {
        $page = Request::input('page');
        $pageKey ??= Config::get('larepo.request_page_key');

        $perPageDefault = Config::get('larepo.per_page_default');
        $perPage = Request::input('perPage', $perPageDefault);
        $perPageKey ??= Config::get('larepo.request_per_page_key');

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
     * @param  int|null $page
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

        $perPageMax = Config::get('larepo.per_page_max');
        $perPageRules = ['integer', 'min:1', 'max:' . $perPageMax];
        $pageRules = ['integer', 'min:1'];

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
