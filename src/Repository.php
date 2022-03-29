<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Contracts\RepositoryStrategyContract;

abstract class Repository implements RepositoryContract
{
    /**
     * The repository strategy.
     *
     * @var RepositoryStrategyContract
     */
    protected RepositoryStrategyContract $strategy;

    /**
     * Creates a repository strategy object.
     *
     * @return RepositoryStrategyContract
     */
    abstract public function createStrategy(): RepositoryStrategyContract;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->strategy = $this->createStrategy();
    }

    /** @inheritdoc */
    public function offset(int $offset): static
    {
        $this->strategy->offset($offset);

        return $this;
    }

    /** @inheritdoc */
    public function limit(int $count): static
    {
        $this->strategy->limit($count);

        return $this;
    }

    /** @inheritdoc */
    public function reset(): static
    {
        $this->strategy->reset();

        return $this;
    }

    /** @inheritdoc */
    public function get(): Collection
    {
        return $this->strategy->get();
    }

    /** @inheritdoc */
    public function paginate(PaginationContract $pagination): Paginator
    {
        return $this->strategy->paginate($pagination);
    }

    /** @inheritdoc */
    public function cursor(): LazyCollection
    {
        return $this->strategy->cursor();
    }

    /** @inheritdoc */
    public function lazy(int $chunkSize = 1000): LazyCollection
    {
        return $this->strategy->lazy($chunkSize);
    }

    /** @inheritdoc */
    public function count(): int
    {
        return $this->strategy->count();
    }

    /** @inheritdoc */
    public function exists(): bool
    {
        return $this->strategy->exists();
    }

    /** @inheritdoc */
    public function find(int|string $id): ?object
    {
        return $this->strategy->find($id);
    }

    /** @inheritdoc */
    public function first(): ?object
    {
        return $this->strategy->first();
    }

    /** @inheritdoc */
    public function addCriteria(CriteriaContract $criteria): static
    {
        $this->strategy->addCriteria($criteria);

        return $this;
    }

    /** @inheritdoc */
    public function setCriteria(?CriteriaContract $criteria): static
    {
        $this->strategy->setCriteria($criteria);

        return $this;
    }

    /** @inheritdoc */
    public function getCriteria(): CriteriaContract
    {
        return $this->strategy->getCriteria();
    }

    /** @inheritdoc */
    public function loadMissing(object $records, CriteriaContract $criteria): void
    {
        $this->strategy->loadMissing($records, $criteria);
    }
}
