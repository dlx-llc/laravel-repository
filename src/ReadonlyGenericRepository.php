<?php

namespace LaravelRepository;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;
use LaravelRepository\Contracts\DbDriverContract;
use LaravelRepository\Contracts\ReadonlyRepositoryContract;

class ReadonlyGenericRepository implements ReadonlyRepositoryContract
{
    /**
     * The database driver.
     *
     * @var DbDriverContract
     */
    protected DbDriverContract $db;

    /**
     * Creates a new instance of this class.
     *
     * @param  mixed $dbContext
     * @return static
     */
    public static function make(object $dbContext): static
    {
        return new static($dbContext);
    }

    /**
     * Constructor.
     *
     * @param  object $dbContext
     * @return void
     */
    public function __construct(object $dbContext)
    {
        $this->db = DbDriverFactory::create($dbContext);
    }

    /** @inheritdoc */
    public function select(string ...$attrs): static
    {
        $this->db->select(...$attrs);

        return $this;
    }

    /** @inheritdoc */
    public function with(string|array $relations, \Closure $callback = null): static
    {
        $this->db->with($relations, $callback);

        return $this;
    }

    /** @inheritdoc */
    public function withCount(array $relations): static
    {
        $this->db->withCount($relations);

        return $this;
    }

    /** @inheritdoc */
    public function offset(int $offset): static
    {
        $this->db->offset($offset);

        return $this;
    }

    /** @inheritdoc */
    public function limit(int $count): static
    {
        $this->db->limit($count);

        return $this;
    }

    /** @inheritdoc */
    public function search(SearchCriteria $query): static
    {
        $this->db->search($query);

        return $this;
    }

    /** @inheritdoc */
    public function get(): Collection
    {
        return $this->db->get();
    }

    /** @inheritdoc */
    public function paginate(Pagination $pagination): Paginator
    {
        return $this->db->paginate($pagination);
    }

    /** @inheritdoc */
    public function cursor(): LazyCollection
    {
        return $this->db->cursor();
    }

    /** @inheritdoc */
    public function lazy(int $chunkSize = 1000): LazyCollection
    {
        return $this->db->lazy();
    }

    /** @inheritdoc */
    public function count(): int
    {
        return $this->db->count();
    }

    /** @inheritdoc */
    public function find(int|string $id): mixed
    {
        return $this->db->find($id);
    }

    /** @inheritdoc */
    public function first(): mixed
    {
        return $this->db->first();
    }
}
