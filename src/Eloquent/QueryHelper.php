<?php

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class QueryHelper
{
    /**
     * The single instance of this class.
     *
     * @var static|null
     */
    private static ?QueryHelper $instance = null;

    /**
     * Returns an instance of this class.
     *
     * @return static
     */
    public static function instance(): static
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Creates an instance of this class.
     *
     * @return void
     */
    private function __construct()
    {
        //
    }

    /**
     * Prevents an ambiguous query execution by adding the main table name to
     * the column names in the query where no table name is specified.
     * For now, checks only order and where clauses.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @return void
     */
    public function preventAmbiguousQuery(QueryBuilder|EloquentBuilder $query): void
    {
        if (is_a($query, EloquentBuilder::class)) {
            $query = $query->getQuery();
        }

        if (empty($query->joins)) {
            return;
        }

        if ($table = $this->tableName($query)) {
            $this->addTableNameToSelect($query, $table);
            $this->addTableNameToWheres($query, $table);
            $this->addTableNameToOrders($query, $table);
        }
    }

    /**
     * Returns the query table name.
     *
     * @param  QueryBuilder|EloquentBuilder|JoinClause $query
     * @return string
     */
    public function tableName(QueryBuilder|EloquentBuilder|JoinClause $query): ?string
    {
        if (is_a($query, EloquentBuilder::class)) {
            return $query->getModel()->getTable();
        }

        $table = $query->from ?? $query->table;

        if (!is_string($table)) {
            $table = (string) $table;
        }

        // Turns "original_table as name_to_use" to "name_to_use"
        if (str_contains($table, ' ')) {
            $lastSpacePos = strrpos($table, ' ');
            $table = substr($table, $lastSpacePos + 1);
        }

        return $table;
    }

    /**
     * Adds table name to the select statement.
     *
     * @param  QueryBuilder $query
     * @param  string $table
     * @return void
     */
    protected function addTableNameToSelect(QueryBuilder $query, string $table): void
    {
        if (empty($query->columns)) {
            $query->select("{$table}.*");
        } else {
            $prepared = [];

            foreach ($query->columns as $column) {
                if (!is_string($column) || str_contains($column, '.')) {
                    $prepared[] = $column;
                } else {
                    $prepared[] = "{$table}.{$column}";
                }
            }

            $query->select($prepared);
        }
    }

    /**
     * Adds table name to the generally stated order clause column names.
     *
     * @param  QueryBuilder $query
     * @param  string $table
     * @return void
     */
    protected function addTableNameToOrders(QueryBuilder $query, string $table): void
    {
        if (!$query->orders) {
            return;
        }

        foreach ($query->orders as $i => $item) {
            if (isset($item['column']) && !str_contains($item['column'], '.')) {
                $query->orders[$i]['column'] = $table . '.' . $item['column'];
            } elseif (isset($item['query']) && is_a($item['query'], QueryBuilder::class)) {
                $table = $this->tableName($item['query']);
                $this->addTableNameToOrders($item['query'], $table);
            }
        }
    }

    /**
     * Adds table name to the generally stated where clause column names.
     *
     * @param  QueryBuilder $query
     * @param  string $table
     * @return void
     */
    protected function addTableNameToWheres(QueryBuilder $query, string $table): void
    {
        if (!$query->wheres) {
            return;
        }

        foreach ($query->wheres as $i => $item) {
            if (isset($item['column']) && !str_contains($item['column'], '.')) {
                $query->wheres[$i]['column'] = $table . '.' . $item['column'];
            } elseif (isset($item['query']) && is_a($item['query'], QueryBuilder::class)) {
                $this->addTableNameToWheres(
                    $item['query'],
                    $this->tableName($item['query'])
                );
            }
        }
    }
}
