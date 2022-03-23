<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Enums\FilterMode;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

final class FilterFactory
{
    /**
     * A registry of filter modes and corresponding filter class names.
     *
     * @var array
     */
    public array $registry = [
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
        $filterClass = self::getClass($mode);

        if (!$filterClass) {
            throw new \Exception(__('larepo::exceptions.undefined_repo_filter_mode'));
        }

        $attr = App::makeWith(DataAttrContract::class, [$attr]);

        return new $filterClass($attr, $mode, $value, $operator);
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
        ClassUtils::checkClassExists($filterClass);
        ClassUtils::checkClassImplements($filterClass, FilterContract::class);

        $factory = self::getInstance();
        $factory->registry[$mode] = $filterClass;
    }

    /**
     * Checks if the given filter mode is registered.
     *
     * @param  string $mode
     * @return bool
     */
    public static function modeRegistered(string $mode): bool
    {
        return !is_null(self::getClass($mode));
    }

    /**
     * Returns the corresponding filter class for the given filter mode.
     *
     * @param  string $mode
     * @return string|null
     */
    public static function getClass(string $mode): ?string
    {
        return self::getInstance()->registry[$mode] ?? null;
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
        $mode = array_search($class, $factory->registry, true) ?: null;

        return $mode;
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
}
