<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\PaginationContract;

class Pagination implements PaginationContract
{
    /** @inheritdoc */
    public function __construct(
        protected int $page,
        protected int $perPage,
        protected string $pageName,
        protected string $perPageName
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
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /** @inheritdoc */
    public function setPageName(string $name): static
    {
        $this->pageName = $name;

        return $this;
    }

    /** @inheritdoc */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /** @inheritdoc */
    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /** @inheritdoc */
    public function getPerPageName(): string
    {
        return $this->perPageName;
    }

    /** @inheritdoc */
    public function setPerPageName(string $name): static
    {
        $this->perPageName = $name;

        return $this;
    }
}
