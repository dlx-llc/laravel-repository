<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\App;
use LaravelRepository\Enums\FilterMode;
use LaravelRepository\Enums\FilterOperator;
use LaravelRepository\Contracts\FilterContract;
use LaravelRepository\Contracts\DataAttrContract;

final class FilterFactory
{
    /**
     * A registry of filter modes and corresponding filter class names.
     *
     * @var array
     */
    private array $registry = [
        FilterMode::IS_LIKE => Filters\IsLikeFilter::class,
        FilterMode::IS_NOT_LIKE => Filters\IsNotLikeFilter::class,
        FilterMode::IS_GREATER => Filters\IsGreaterFilter::class,
        FilterMode::IS_GREATER_OR_EQUAL => Filters\IsGreaterOrEqualFilter::class,
        FilterMode::IS_LOWER => Filters\IsLowerFilter::class,
        FilterMode::IS_LOWER_OR_EQUAL => Filters\IsLowerOrEqualFilter::class,
        FilterMode::IN_RANGE => Filters\InRangeFilter::class,
        FilterMode::NOT_IN_RANGE => Filters\NotInRangeFilter::class,
        FilterMode::EQUALS_TO => Filters\EqualsToFilter::class,
        FilterMode::NOT_EQUALS_TO => Filters\NotEqualsToFilter::class,
        FilterMode::INCLUDED_IN => Filters\IncludedInFilter::class,
        FilterMode::NOT_INCLUDED_IN => Filters\NotIncludedInFilter::class,
        FilterMode::CONTAINS => Filters\ContainsFilter::class,
        FilterMode::DOES_NOT_CONTAIN => Filters\DoesNotContainFilter::class,
        FilterMode::IS_NULL => Filters\IsNullFilter::class,
        FilterMode::IS_NOT_NULL => Filters\IsNotNullFilter::class,
        FilterMode::EXISTS => Filters\RelationExistsFilter::class,
        FilterMode::DOES_NOT_EXIST => Filters\RelationDoesNotExistFilter::class,
    ];

    /**
     * The only instance of this class.
     *
     * @var FilterFactory|null
     */
    private static ?FilterFactory $instance = null;

    /**
     * Creates a filter object of the given mode.
     *
     * @param  string $mode
     * @param  string $attr
     * @param  mixed $value
     * @param  string $operator
     * @return FilterContract
     * @throws \Exception
     */
    public static function create(
        string $mode,
        string $attr,
        mixed $value,
        string $operator = FilterOperator::AND
    ): FilterContract {
        $factory = self::getInstance();
        $filterClass = $factory->matchClass($mode);

        if (!$filterClass) {
            throw new \Exception(__('lrepo::exceptions.undefined_repo_filter_mode'));
        }

        $attr = App::makeWith(DataAttrContract::class, ['name' => $attr]);

        return new $filterClass($attr, $value, $operator);
    }

    /**
     * Adds a filter mode to filter class pair.
     *
     * @param  string $mode
     * @param  string $filterClass
     * @return void
     */
    public static function register(string $mode, string $filterClass): void
    {
        $factory = self::getInstance();
        $factory->addToRegistry($mode, $filterClass);
    }

    /**
     * Checks if the given filter mode is registered.
     *
     * @param  string $mode
     * @return bool
     */
    public static function modeRegistered(string $mode): bool
    {
        $factory = self::getInstance();

        return !is_null($factory->matchClass($mode));
    }

    /**
     * Returns the corresponding filter class for the given filter mode.
     *
     * @param  string $mode
     * @return string|null
     */
    public static function getClass(string $mode): ?string
    {
        $factory = self::getInstance();

        return $factory->matchClass($mode);
    }

    /**
     * Returns the corresponding filter mode for the given filter class.
     *
     * @param  string $class
     * @return string|null
     */
    public static function getMode(string $class): ?string
    {
        $factory = self::getInstance();

        return $factory->matchMode($class);
    }

    /**
     * Returns the instance of this class.
     *
     * @return self
     */
    private static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class constructor.
     *
     * @return void
     */
    private function __construct()
    {
        // Prevents instantiation outside the class.
    }

    /**
     * Returns the matching filter class.
     *
     * @param  string $mode
     * @return string|null
     */
    public function matchClass(string $mode): ?string
    {
        return $this->registry[$mode] ?? null;
    }

    /**
     * Returns the matching filter mode.
     *
     * @param  string $class
     * @return string|null
     */
    public function matchMode(string $class): ?string
    {
        return array_search($class, $this->registry, true) ?: null;
    }

    /**
     * Adds/updates a filter mode to filter class pair in registry.
     *
     * @param  string $mode
     * @param  string $filterClass
     * @return void
     * @throws \Exception
     */
    public function addToRegistry(string $mode, string $filterClass): void
    {
        if (!is_subclass_of($filterClass, FilterContract::class)) {
            throw new \Exception(__('lrepo::exceptions.does_not_implement', [
                'class' => $filterClass,
                'interface' => FilterContract::class,
            ]));
        }

        $this->registry[$mode] = $filterClass;
    }
}
