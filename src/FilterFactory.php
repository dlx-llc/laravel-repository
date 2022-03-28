<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

final class FilterFactory
{
    /**
     * A registry of filter operators and corresponding filter class names.
     *
     * @var array
     */
    public array $registry = [
        FilterOperator::IS_LIKE => Filters\IsLikeFilter::class,
        FilterOperator::IS_NOT_LIKE => Filters\IsNotLikeFilter::class,
        FilterOperator::IS_GREATER => Filters\IsGreaterFilter::class,
        FilterOperator::IS_GREATER_OR_EQUAL => Filters\IsGreaterOrEqualFilter::class,
        FilterOperator::IS_LOWER => Filters\IsLowerFilter::class,
        FilterOperator::IS_LOWER_OR_EQUAL => Filters\IsLowerOrEqualFilter::class,
        FilterOperator::IN_RANGE => Filters\InRangeFilter::class,
        FilterOperator::NOT_IN_RANGE => Filters\NotInRangeFilter::class,
        FilterOperator::EQUALS_TO => Filters\EqualsToFilter::class,
        FilterOperator::NOT_EQUALS_TO => Filters\NotEqualsToFilter::class,
        FilterOperator::INCLUDED_IN => Filters\IncludedInFilter::class,
        FilterOperator::NOT_INCLUDED_IN => Filters\NotIncludedInFilter::class,
        FilterOperator::CONTAINS => Filters\ContainsFilter::class,
        FilterOperator::DOES_NOT_CONTAIN => Filters\DoesNotContainFilter::class,
        FilterOperator::IS_NULL => Filters\IsNullFilter::class,
        FilterOperator::IS_NOT_NULL => Filters\IsNotNullFilter::class,
        FilterOperator::EXISTS => Filters\RelationExistsFilter::class,
        FilterOperator::DOES_NOT_EXIST => Filters\RelationDoesNotExistFilter::class,
    ];

    /**
     * The only instance of this class.
     *
     * @var FilterFactory|null
     */
    private static ?FilterFactory $instance = null;

    /**
     * Creates a filter object.
     *
     * @param  string $operator
     * @param  string $attr
     * @param  mixed $value
     * @param  string $boolean
     * @return FilterContract
     * @throws \Exception
     */
    public static function create(
        string $operator,
        string $attr,
        mixed $value,
        string $boolean = BooleanOperator::AND
    ): FilterContract {
        $filterClass = self::getClass($operator);

        if (!$filterClass) {
            throw new \Exception(__('larepo::exceptions.undefined_repo_filter_operator'));
        }

        $attr = App::makeWith(DataAttrContract::class, [$attr]);

        return new $filterClass($attr, $operator, $value, $boolean);
    }

    /**
     * Adds a filter operator to filter class pair.
     *
     * @param  string $operator
     * @param  string $filterClass
     * @return void
     */
    public static function register(string $operator, string $filterClass): void
    {
        ClassUtils::checkClassExists($filterClass);
        ClassUtils::checkClassImplements($filterClass, FilterContract::class);

        $factory = self::getInstance();
        $factory->registry[$operator] = $filterClass;
    }

    /**
     * Checks if the given filter operator is registered.
     *
     * @param  string $operator
     * @return bool
     */
    public static function operatorRegistered(string $operator): bool
    {
        return !is_null(self::getClass($operator));
    }

    /**
     * Returns the corresponding filter class for the given filter operator.
     *
     * @param  string $operator
     * @return string|null
     */
    public static function getClass(string $operator): ?string
    {
        return self::getInstance()->registry[$operator] ?? null;
    }

    /**
     * Returns the corresponding filter operator for the given filter class.
     *
     * @param  string $class
     * @return string|null
     */
    public static function getOperator(string $class): ?string
    {
        $factory = self::getInstance();
        $operator = array_search($class, $factory->registry, true) ?: null;

        return $operator;
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
