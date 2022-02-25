<?php

namespace LaravelRepository;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use LaravelRepository\Drivers\EloquentDriver;
use Illuminate\Contracts\Pagination\Paginator;
use LaravelRepository\Contracts\DbDriverContract;
use LaravelRepository\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class GenericRepository implements RepositoryContract
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
    public static function make(mixed $dbContext): static
    {
        return new static($dbContext);
    }

    /**
     * Constructor.
     *
     * @param  mixed $dbContext
     * @return void
     */
    public function __construct(mixed $dbContext)
    {
        $dbContextType = get_class($dbContext);

        $this->db = match ($dbContextType) {
            EloquentBuilder::class => EloquentDriver::init($dbContext),
            default => throw new \Exception(
                __(
                    'lrepo::exceptions.illegal_filters_collection_item',
                    ['type' => $dbContextType]
                )
            ),
        };
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

    /** @inheritdoc */
    public function create(array $attributes): object
    {
        return $this->db->create($attributes);
    }

    /** @inheritdoc */
    public function update(object $model, array $attributes): void
    {
        $this->db->update($model, $attributes);
    }

    /** @inheritdoc */
    public function delete(object $model): void
    {
        $this->db->delete($model);
    }
}
