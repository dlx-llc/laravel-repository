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
     * @param  string|null $pageName
     * @param  string|null $perPageName
     * @return PaginationContract
     */
    public static function create(
        int $page,
        int $perPage,
        ?string $pageName = null,
        ?string $perPageName = null
    ): PaginationContract {
        $pageName ??= Config::get('larepo.request_page_key');
        $perPageName ??= Config::get('larepo.request_per_page_key');

        return App::makeWith(PaginationContract::class, [
            'page' => $page,
            'perPage' => $perPage,
            'pageName' => $pageName,
            'perPageName' => $perPageName,
        ]);
    }

    /**
     * Crates a new pagination object using parameters passed via request.
     *
     * @param  bool $validate
     * @param  bool $require
     * @param  string|null $pageName
     * @param  string|null $perPageName
     * @return PaginationContract|null
     */
    public static function createFromRequest(
        bool $validate = true,
        bool $require = true,
        ?string $pageName = null,
        ?string $perPageName = null
    ): ?PaginationContract {
        $pageName ??= Config::get('larepo.request_page_key');
        $page = Request::input($pageName);

        $perPageName ??= Config::get('larepo.request_per_page_key');
        $perPageDefault = Config::get('larepo.per_page_default');
        $perPage = Request::input($perPageName, $perPageDefault);

        if (!$require && !$page) {
            return null;
        } elseif ($validate) {
            self::validate($pageName, $perPageName, $page, $perPage, $require);
        }

        return self::create($page, $perPage, $pageName, $perPageName);
    }

    /**
     * Validates pagination params.
     *
     * @param  string $pageName
     * @param  string $perPageName
     * @param  int|null $page
     * @param  int $perPage
     * @return void
     * @throws ValidationException
     */
    protected static function validate(
        string $pageName,
        string $perPageName,
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
            $pageName => $page,
            $perPageName => $perPage,
        ], [
            $pageName => $pageRules,
            $perPageName => $perPageRules,
        ])->validate();
    }
}
