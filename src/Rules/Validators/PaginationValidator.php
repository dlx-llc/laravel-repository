<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules\Validators;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Deluxetech\LaRepo\Contracts\PaginationContract;

class PaginationValidator
{
    /**
     * The request page query parameter name.
     */
    protected string $pageName;

    /**
     * The request page query parameter name.
     */
    protected string $perPageName;

    /**
     * The validated data.
     *
     * @var array<string,mixed>
     */
    protected array $validated = [];

    public function __construct(
        ?string $pageName = null,
        ?string $perPageName = null,
    ) {
        $this->pageName = $pageName ?? Config::get('larepo.request_page_key');
        $this->perPageName = $perPageName ?? Config::get('larepo.request_per_page_key');
    }

    /**
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
     */
    public function createFromValidated(): ?PaginationContract
    {
        if (!$this->validated) {
            return null;
        }

        return App::make(PaginationContract::class, [
            'page' => $this->validated[$this->pageName],
            'perPage' => $this->validated[$this->perPageName],
            'pageName' => $this->pageName,
            'perPageName' => $this->perPageName,
        ]);
    }
}
