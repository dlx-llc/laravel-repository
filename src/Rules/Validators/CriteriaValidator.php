<?php

namespace Deluxetech\LaRepo\Rules\Validators;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Deluxetech\LaRepo\Rules\RepositorySorting;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Rules\RepositoryFiltration;
use Deluxetech\LaRepo\Rules\RepositoryTextSearch;

class CriteriaValidator
{
    /**
     * The criteria text search parameter request key.
     *
     * @var string
     */
    protected string $textSearchKey;

    /**
     * The criteria sorting parameter request key.
     *
     * @var string
     */
    protected string $sortingKey;

    /**
     * The criteria filtration parameter request key.
     *
     * @var string
     */
    protected string $filtersKey;

    /**
     * The validated data.
     *
     * @var array
     */
    protected array $validated = [];

    /**
     * Class constructor.
     *
     * @param  string|null $textSearchKey
     * @param  string|null $sortingKey
     * @param  string|null $filtersKey
     * @return void
     */
    public function __construct(
        ?string $textSearchKey = null,
        ?string $sortingKey = null,
        ?string $filtersKey = null
    ) {
        $this->textSearchKey = $textSearchKey ?? Config::get('larepo.request_text_search_key');
        $this->sortingKey = $sortingKey ?? Config::get('larepo.request_sorting_key');
        $this->filtersKey = $filtersKey ?? Config::get('larepo.request_filters_key');
    }

    /**
     * Validates criteria params.
     *
     * @return void
     * @throws ValidationException
     */
    public function validate(): void
    {
        $textSearch = Request::input($this->textSearchKey);
        $sorting = Request::input($this->sortingKey);
        $filters = Request::input($this->filtersKey);

        $data = $rules = [];

        if (!is_null($textSearch)) {
            $this->setDotStrAsAssoc($data, $this->textSearchKey, $textSearch);
            $rules[$this->textSearchKey] = [new RepositoryTextSearch()];
        }

        if (!is_null($sorting)) {
            $this->setDotStrAsAssoc($data, $this->sortingKey, $sorting);
            $rules[$this->sortingKey] = [new RepositorySorting()];
        }

        if (!is_null($filters)) {
            $this->setDotStrAsAssoc($data, $this->filtersKey, $filters);
            $rules[$this->filtersKey] = [new RepositoryFiltration()];
        }

        if ($data && $rules) {
            $this->validated = Validator::make($data, $rules)->validate();
            $this->validated = Arr::dot($this->validated);
        }
    }

    /**
     * Fills the validated parameters into the given criteria object.
     *
     * @param  CriteriaContract $criteria
     * @return void
     */
    public function fillValidated(CriteriaContract $criteria): void
    {
        if (isset($this->validated[$this->textSearchKey])) {
            $criteria->setTextSearchRaw($this->validated[$this->textSearchKey]);
        }

        if (isset($this->validated[$this->sortingKey])) {
            $criteria->setSortingRaw($this->validated[$this->sortingKey]);
        }

        if (isset($this->validated[$this->filtersKey])) {
            $criteria->setFiltersRaw($this->validated[$this->filtersKey]);
        }
    }

    /**
     * Merges dot notated string in the given array as an associative array.
     *
     * @param  array &$into
     * @param  string $dotStr
     * @param  mixed $value
     * @return void
     */
    protected function setDotStrAsAssoc(array &$into, string $str, mixed $value): void
    {
        $pieces = explode('.', $str);
        $current = &$into[array_shift($pieces)];

        foreach ($pieces as $piece) {
            $current = &$current[$piece];
        }

        $current = $value;
    }
}
