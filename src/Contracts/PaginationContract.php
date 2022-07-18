<?php

namespace Deluxetech\LaRepo\Contracts;

interface PaginationContract
{
    /**
     * Class constructor.
     *
     * @param  int $page
     * @param  int $perPage
     * @param  string $pageName
     * @param  string $perPageName
     */
    public function __construct(
        int $page,
        int $perPage,
        string $pageName,
        string $perPageName
    );

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
     * Returns the page query parameter name.
     *
     * @return string
     */
    public function getPageName(): string;

    /**
     * Specifies the page query parameter name.
     *
     * @param  string $name
     * @return static
     */
    public function setPageName(string $name): static;

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

    /**
     * Returns the per page query parameter name.
     *
     * @return string
     */
    public function getPerPageName(): string;

    /**
     * Specifies the per page query parameter name.
     *
     * @param  string $name
     * @return static
     */
    public function setPerPageName(string $name): static;
}
