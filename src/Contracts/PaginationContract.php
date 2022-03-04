<?php

namespace LaravelRepository\Contracts;

interface PaginationContract
{
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
    ): ?static;

    /**
     * Creates a new instance of this class from a raw pagination string.
     *
     * @param  string $rawStr
     * @return static
     */
    public static function makeRaw(string $rawStr): static;

    /**
     * Class constructor.
     *
     * @param  int $perPage
     * @param  int $page
     * @return void
     */
    public function __construct(int $perPage, int $page);

    /**
     * Returns the pagination page.
     *
     * @return int
     */
    public function getPage(): int;

    /**
     * Specifies the pagination page.
     *
     * @return int
     */
    public function setPage(int $page): static;

    /**
     * Returns the number of records per page.
     *
     * @return int
     */
    public function getPerPage(): int;

    /**
     * Specifies the number of records per page.
     *
     * @return int
     */
    public function setPerPage(int $perPage): static;
}
