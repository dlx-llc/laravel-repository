<?php

namespace LaravelRepository;

use LaravelRepository\Drivers\EloquentDriver;
use LaravelRepository\Contracts\DbDriverContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @method GenericRepository select(string ...$attrs)  Sets the data attributes that should be fetched.
 * @method GenericRepository distinct()  Specifies that duplicate results should be excluded.
 * @method GenericRepository with(string|array $relations, \Closure $callback = null)  Sets the relationships that should be eager loaded.
 * @method GenericRepository withCount(array $relations)  Sets the relationship counts that should be loaded with data.
 * @method GenericRepository limit(int $count)  Sets a limit for the number of results.
 * @method GenericRepository search(SearchCriteria $query)  Sets the search criteria.
 * @method \Illuminate\Support\Collection get()  Fetches query results.
 * @method \Illuminate\Contracts\Pagination\Paginator paginate(Pagination $pagination)  Fetches paginated query results.
 * @method \Illuminate\Support\LazyCollection cursor()  Fetches query results via lazy collection.
 * @method \Illuminate\Support\LazyCollection lazy(int $chunkSize = 1000)  Fetches query results in chunks via lazy collection.
 * @method int count()  Returns the number of query results.
 * @method mixed find(int|string $id)  Fetches a single result from the query by ID.
 * @method mixed first()  Fetches the first result from the query.
 * @method mixed create(array $attributes)  Creates a new data model and returns the instance.
 * @method void update(mixed $model, array $attributes)  Updates the given data model.
 * @method void delete(mixed $model)  Deletes the given data model.
 */
class GenericRepository
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

    /**
     * Fires when an attempt is made to access private or non-existent methods.
     *
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->db, $name)) {
            $result = $this->db->{$name}(...$arguments);

            return is_a($result, DbDriverContract::class) ? $this : $result;
        }

        throw new \Exception(
            __(
                'lrepo::exceptions.class_method_missing',
                ['class' => static::class, 'method' => $name]
            )
        );
    }
}
