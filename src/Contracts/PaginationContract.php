<?php

namespace Deluxetech\LaRepo\Contracts;

interface PaginationContract
{
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
