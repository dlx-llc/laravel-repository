<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\LoadContextContract;
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
     * The data attributes mapper.
     *
     * @var DataMapperContract|null
     */
    protected ?DataMapperContract $dataMapper = null;

    /**
     * Creates a strategy for the repository.
     *
     * @return RepositoryStrategyContract
     */
    abstract protected function createStrategy(): RepositoryStrategyContract;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
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
    public function setDataMapper(?DataMapperContract $dataMapper): static
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }

    /** @inheritdoc */
    public function setLoadContext(LoadContextContract $context): static
    {
        $this->applyLoadContext($this->strategy, $context);

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
    public function search(SearchCriteriaContract $criteria): static
    {
        if ($this->dataMapper) {
            $this->dataMapper->applyOnSearchCriteria($criteria);
        }

        $this->strategy->search($criteria);

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

    /**
     * Recursively loads the required relations.
     *
     * @param  mixed $query
     * @param  LoadContextContract $context
     * @return void
     */
    protected function applyLoadContext(mixed $query, LoadContextContract $context): void
    {
        if ($attrs = $context->getAttributes()) {
            $query->select(...$attrs);
        }

        foreach ($context->getRelations() as $key => $value) {
            if (is_int($key)) {
                $query->with($value);
            } elseif (is_string($key)) {
                if (is_subclass_of($value, LoadContextContract::class)) {
                    $query->with($key, function ($query) use ($value) {
                        $this->applyLoadContext($query, $value);
                    });
                } else {
                    $query->with($key);
                }
            }
        }

        if ($counts = $context->getRelationCounts()) {
            $query->withCount($counts);
        }
    }
}
