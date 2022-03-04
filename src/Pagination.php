<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use LaravelRepository\Rules\RepositoryPagination;
use LaravelRepository\Contracts\PaginationContract;
use LaravelRepository\Contracts\PaginationFormatterContract;

class Pagination implements PaginationContract
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
        $params = App::make(PaginationFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_pagination_string'));
        }

        return static::make($params[1], $params[0]);
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

    /** @inheritdoc */
    public function getPage(): int
    {
        return $this->page;
    }

    /** @inheritdoc */
    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    /** @inheritdoc */
    public function getPerPage(): int
    {
        return $this->getPerPage();
    }

    /** @inheritdoc */
    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }
}
