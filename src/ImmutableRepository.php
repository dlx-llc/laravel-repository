<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;
use Deluxetech\LaRepo\Contracts\RepositoryStrategyContract;
use Deluxetech\LaRepo\Contracts\ImmutableRepositoryContract;

abstract class ImmutableRepository implements ImmutableRepositoryContract
{
    /**
     * The repository strategy.
     *
     * @var RepositoryStrategyContract
     */
    protected RepositoryStrategyContract $strategy;

    /**
     * Creates a strategy for the repository.
     *
     * @return RepositoryStrategyContract
     */
    abstract protected function createStrategy(): RepositoryStrategyContract;

    /**
     * Class constructor.
     *
     * @param  DomainMapper $domainMapper
     * @return void
     */
    public function __construct(protected DomainMapper $domainMapper)
    {
        $strategy = $this->createStrategy();
        $this->setStrategy($strategy);
    }

    /** @inheritdoc */
    public function setStrategy(RepositoryStrategyContract $strategy): static
    {
        $this->strategy = $strategy;

        return $this;
    }

    /** @inheritdoc */
    public function select(string ...$attrs): static
    {
        $this->strategy->select(...$attrs);

        return $this;
    }

    /** @inheritdoc */
    public function with(string|array $relations, \Closure $callback = null): static
    {
        $this->strategy->with($relations, $callback);

        return $this;
    }

    /** @inheritdoc */
    public function withCount(array $relations): static
    {
        $this->strategy->withCount($relations);

        return $this;
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
    public function search(SearchCriteriaContract $query): static
    {
        $this->strategy->search($query);

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
        return $this->strategy->lazy();
    }

    /** @inheritdoc */
    public function count(): int
    {
        return $this->strategy->count();
    }

    /** @inheritdoc */
    public function find(int|string $id): mixed
    {
        return $this->strategy->find($id);
    }

    /** @inheritdoc */
    public function first(): mixed
    {
        return $this->strategy->first();
    }
}
