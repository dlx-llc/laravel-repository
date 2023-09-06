<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\PaginationContract;

class Pagination implements PaginationContract
{
    public function __construct(
        protected int $page,
        protected int $perPage,
        protected string $pageName,
        protected string $perPageName
    ) {
        //
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function setPageName(string $name): static
    {
        $this->pageName = $name;

        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPerPageName(): string
    {
        return $this->perPageName;
    }

    public function setPerPageName(string $name): static
    {
        $this->perPageName = $name;

        return $this;
    }
}
