<?php

namespace LaravelRepository;

use LaravelRepository\Contracts\PaginationContract;

class Pagination implements PaginationContract
{
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
