<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface PaginationContract
{
    public function __construct(
        int $page,
        int $perPage,
        string $pageName,
        string $perPageName,
    );

    public function getPage(): int;

    public function setPage(int $page): static;

    /**
     * Returns the page query parameter name.
     */
    public function getPageName(): string;

    /**
     * Specifies the page query parameter name.
     */
    public function setPageName(string $name): static;

    public function getPerPage(): int;

    public function setPerPage(int $perPage): static;

    /**
     * Returns the per page query parameter name.
     */
    public function getPerPageName(): string;

    /**
     * Specifies the per page query parameter name.
     */
    public function setPerPageName(string $name): static;
}
