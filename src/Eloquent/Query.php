<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Query
{
    private QueryBuilder $builder;
    private string $mainTableName;
    private string $mainTableAlias;

    /**
     * Database table name to the alias name map.
     * Contains the query main table and joined tables.
     *
     * @var array<string,string>
     */
    private array $tables = [];

    /**
     * Database table name to its columns map.
     *
     * @var array<string,array<string>>
     */
    private array $columns = [];

    public function __construct(
        private QueryBuilder|EloquentBuilder|JoinClause $query,
        private bool $isNested = false,
    ) {
        $this->builder = is_a($query, EloquentBuilder::class)
            ? $query->getQuery()
            : $query;

        $this->setMainTable();
        $this->setJoinTables();
    }

    public function hasJoins(): bool
    {
        return !empty($this->builder->joins);
    }

    /**
     * Adds table name to columns used in selects, where conditions and orders.
     */
    public function preventColumnAmbiguity(): void
    {
        if ($this->isNested || $this->hasJoins()) {
            $this->addTableNameToSelect();
            $this->addTableNameToOrders();
        }

        $this->addTableNameToWheres();
    }

    private function addTableNameToSelect(): void
    {
        if (empty($this->builder->columns)) {
            $this->builder->select("{$this->mainTableAlias}.*");
        } else {
            foreach ($this->builder->columns as $i => $column) {
                if (!is_string($column)) {
                    continue;
                } elseif ($column === '*') {
                    $this->builder->columns[$i] = "{$this->mainTableAlias}.*";
                } elseif (!str_contains($column, '.')) {
                    $tableName = $this->getColumnTable($column);
                    $this->builder->columns[$i] = "{$tableName}.{$column}";
                }
            }
        }
    }

    private function addTableNameToOrders(): void
    {
        if (!isset($this->builder->orders)) {
            return;
        }

        foreach ($this->builder->orders as $i => $item) {
            if (isset($item['column']) && !str_contains($item['column'], '.')) {
                $column = $item['column'];
                $tableName = $this->getColumnTable($column);
                $this->builder->orders[$i]['column'] = "{$tableName}.{$column}";
            } elseif (isset($item['query']) && is_a($item['query'], QueryBuilder::class)) {
                $subQuery = new static($item['query']);
                $subQuery->preventColumnAmbiguity();
            }
        }
    }

    private function addTableNameToWheres(): void
    {
        if (!isset($this->builder->wheres)) {
            return;
        }

        $shouldAddTableName = $this->isNested || $this->hasJoins();

        foreach ($this->builder->wheres as $i => $item) {
            if (isset($item['column']) && !str_contains($item['column'], '.')) {
                if ($shouldAddTableName) {
                    $column = $item['column'];
                    $tableName = $this->getColumnTable($column);
                    $this->builder->wheres[$i]['column'] = "{$tableName}.{$column}";
                }
            } elseif (isset($item['query']) && is_a($item['query'], QueryBuilder::class)) {
                $isNested = isset($item['type']) && $item['type'] === 'Nested';
                $subQuery = new static($item['query'], $isNested);
                $subQuery->preventColumnAmbiguity();
            }
        }
    }

    private function getColumnTable(string $column): string
    {
        foreach ($this->tables as $tableName => $tableAlias) {
            $tableColumns = $this->getTableColumns($tableName);

            if (in_array($column, $tableColumns, true)) {
                return $tableAlias;
            }
        }

        return $this->mainTableAlias;
    }

    /**
     * @return array<string>
     */
    private function getTableColumns(string $table): array
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = Schema::getColumnListing($table);
        }

        return $this->columns[$table];
    }

    private function setMainTable(): void
    {
        [$this->mainTableName, $this->mainTableAlias] = $this->getTableName($this->builder);
        $this->tables[$this->mainTableName] = $this->mainTableAlias;
    }

    private function setJoinTables(): void
    {
        if (!isset($this->builder->joins)) {
            return;
        }

        foreach ($this->builder->joins as $query) {
            [$tableFrom, $tableAs] = $this->getTableName($query);
            $this->tables[$tableFrom] = $tableAs;
        }
    }

    /**
     * @return array{string,string}
     */
    private function getTableName(QueryBuilder|JoinClause $query): array
    {
        $table = $query->from ?? $query->table ?? '';

        if (str_contains($table, ' ')) {
            $table = str_replace(' as ', ' ', $table);
            $table = str_replace(' AS ', ' ', $table);
            [$from, $as] = explode(' ', $table);
        } else {
            $from = $as = $table;
        }

        return [$from, $as];
    }
}
