<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;

final class FilterRegistry
{
    /**
     * Filter operator to filter class mapping.
     *
     * @var array<string,class-string<FilterContract>>
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
     */
    private static ?self $instance = null;

    private function __construct()
    {
        // Prevents instantiation outside the class.
    }

    /**
     * Registers a new filter operator with the corresponding filter class.
     *
     * @param class-string<FilterContract> $filterClass
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
     */
    public static function isRegistered(string $operator): bool
    {
        return !is_null(self::getClass($operator));
    }

    /**
     * Returns the corresponding filter class for the given filter operator.
     *
     * @return ?class-string<FilterContract<mixed>>
     */
    public static function getClass(string $operator): ?string
    {
        return self::getInstance()->registry[$operator] ?? null;
    }

    /**
     * Returns the corresponding filter operator for the given filter class.
     *
     * @param class-string<FilterContract> $class
     */
    public static function getOperator(string $class): ?string
    {
        $factory = self::getInstance();
        $operator = array_search($class, $factory->registry, true) ?: null;

        return $operator;
    }

    /**
     * Returns the only instance of this class.
     */
    private static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
