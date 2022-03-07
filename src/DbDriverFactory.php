<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Drivers\EloquentDriver;
use Deluxetech\LaRepo\Contracts\DbDriverContract;
use Illuminate\Database\Eloquent\Builder as EloquentContext;

final class DbDriverFactory
{
    /**
     * A registry of DB context and corresponding DB driver class names.
     *
     * @var array
     */
    private array $registry = [
        EloquentContext::class => EloquentDriver::class,
    ];

    /**
     * The only instance of this class.
     *
     * @var DbDriverFactory|null
     */
    private static ?DbDriverFactory $instance = null;

    /**
     * Creates a DB driver object for the given DB context.
     *
     * @param  object $dbContext
     * @return DbDriverContract
     * @throws \Exception
     */
    public static function create(object $dbContext): DbDriverContract
    {
        $factory = self::getInstance();
        $dbContextClass = get_class($dbContext);
        $dbDriverClass = $factory->match($dbContextClass);

        if (!$dbDriverClass) {
            throw new \Exception(__('lrepo::exceptions.db_context_not_supported', [
                'type' => $dbContextClass,
            ]));
        }

        return $dbDriverClass::make($dbContext);
    }

    /**
     * Adds a DB context to DB driver pair.
     *
     * @param  string $dbContextClass
     * @param  string $dbDriverClass
     * @return void
     */
    public static function register(string $dbContextClass, string $dbDriverClass): void
    {
        $factory = self::getInstance();
        $factory->addToRegistry($dbContextClass, $dbDriverClass);
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
     * Returns the matching DB driver class.
     *
     * @param  string $dbContextClass
     * @return string|null
     */
    public function match(string $dbContextClass): ?string
    {
        return $this->registry[$dbContextClass] ?? null;
    }

    /**
     * Adds/updates a DB context to driver pair.
     *
     * @param  string $dbContextClass
     * @param  string $dbDriverClass
     * @return void
     * @throws \Exception
     */
    public function addToRegistry(string $dbContextClass, string $dbDriverClass): void
    {
        if (!class_exists($dbContextClass)) {
            throw new \Exception(__('lrepo::exceptions.class_not_defined', [
                'class' => $dbContextClass,
            ]));
        } elseif (!is_subclass_of($dbDriverClass, DbDriverContract::class)) {
            throw new \Exception(__('lrepo::exceptions.does_not_implement', [
                'class' => $dbDriverClass,
                'interface' => DbDriverContract::class,
            ]));
        }

        $this->registry[$dbContextClass] = $dbDriverClass;
    }
}
