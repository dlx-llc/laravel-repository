<?php

namespace Deluxetech\LaRepo\Rules\Validators;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Deluxetech\LaRepo\Contracts\PaginationContract;

class PaginationValidator
{
    /**
     * The request page input name.
     *
     * @var string
     */
    protected string $pageName;

    /**
     * The request per page input name.
     *
     * @var string
     */
    protected string $perPageName;

    /**
     * The validated data.
     *
     * @var array
     */
    protected array $validated = [];

    /**
     * Class constructor.
     *
     * @param  string|null $pageName
     * @param  string|null $perPageName
     * @return void
     */
    public function __construct(
        ?string $pageName = null,
        ?string $perPageName = null
    ) {
        $this->pageName = $pageName ?? Config::get('larepo.request_page_key');
        $this->perPageName = $perPageName ?? Config::get('larepo.request_per_page_key');
    }

    /**
     * Validates criteria params.
     *
     * @param  bool $require
     * @param  int|null $perPageMax
     * @return void
     * @throws ValidationException
     */
    public function validate(bool $require, ?int $perPageMax = null): void
    {
        $page = Request::input($this->pageName);
        $perPageDefault = Config::get('larepo.per_page_default');
        $perPage = Request::input($this->perPageName, $perPageDefault);

        if (!$require && is_null($page)) {
            return;
        }

        $perPageMax ??= Config::get('larepo.per_page_max');
        $perPageRules = ['integer', 'min:1', 'max:' . $perPageMax];
        $pageRules = ['integer', 'min:1'];

        if ($require) {
            $pageRules[] = ['required'];
        }

        $this->validated = Validator::make([
            $this->pageName => $page,
            $this->perPageName => $perPage,
        ], [
            $this->pageName => $pageRules,
            $this->perPageName => $perPageRules,
        ])->validate();
    }

    /**
     * Creates a pagination object from the validated data.
     *
     * @return PaginationContract
     */
    public function createFromValidated(): PaginationContract
    {
        return App::makeWith(PaginationContract::class, [
            'page' => $this->validated[$this->pageName],
            'perPage' => $this->validated[$this->perPageName],
            'pageName' => $this->pageName,
            'perPageName' => $this->perPageName,
        ]);
    }
}
