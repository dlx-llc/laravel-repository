<?php

namespace Deluxetech\LaRepo\Rules\Validators\Traits;

use Deluxetech\LaRepo\FilterFactory;
use Deluxetech\LaRepo\Enums\BooleanOperator;

trait ValidatesFilters
{
    /**
     * Checks if the given value is a valid filters array.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateFiltersArr(string $attribute, mixed $value): bool
    {
        $errCountBefore = count($this->errors);

        if (!is_array($value)) {
            $this->addError('array', $attribute);
        } else {
            foreach ($value as $i => $item) {
                if (isset($item['items'])) {
                    $this->validateFiltersCollection("{$attribute}.{$i}", $item);
                } else {
                    $this->validateFilter("{$attribute}.{$i}", $item);
                }
            }
        }

        return count($this->errors) <= $errCountBefore;
    }

    /**
     * Checks if the given value is a valid filters collection.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateFiltersCollection(string $attribute, mixed $value): bool
    {
        $errCountBefore = count($this->errors);

        if (!is_array($value)) {
            $this->addError('array', $attribute);
        } else {
            if (array_key_exists('boolean', $value)) {
                $this->validateBooleanOperator("{$attribute}.boolean", $value['boolean']);
            }

            if (empty($value['items'])) {
                $this->addError('required', "{$attribute}.items");
            } else {
                foreach ($value['items'] as $i => $item) {
                    if (isset($item['items'])) {
                        $this->validateFiltersCollection("{$attribute}.items.{$i}", $item);
                    } else {
                        $this->validateFilter("{$attribute}.items.{$i}", $item);
                    }
                }
            }
        }

        return count($this->errors) <= $errCountBefore;
    }

    /**
     * Checks if the given value is a valid filter.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateFilter(string $attribute, mixed $value): bool
    {
        $errCountBefore = count($this->errors);

        if (!is_array($value)) {
            $this->addError('array', $attribute);
        } else {
            if (array_key_exists('boolean', $value)) {
                $this->validateBooleanOperator("{$attribute}.boolean", $value['boolean']);
            }

            $attr = $value['attr'] ?? null;
            $operator = $value['operator'] ?? null;
            $this->validateFilterAttr("{$attribute}.attr", $attr);

            if ($this->validateFilterOperator("{$attribute}.operator", $operator)) {
                $filterVal = $value['value'] ?? null;
                $filterClass = FilterFactory::getClass($operator);

                if ($errors = $filterClass::validateValue($attribute, $filterVal)) {
                    $this->addError(...$errors);
                }
            }
        }

        return count($this->errors) <= $errCountBefore;
    }


    /**
     * Validates the given filter boolean operator.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateBooleanOperator(string $attribute, mixed $value): bool
    {
        if (!in_array($value, BooleanOperator::cases(), true)) {
            $this->addError('in', $attribute);

            return false;
        }

        return true;
    }

    /**
     * Validates the given value to be a valid data attribute.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    protected function validateFilterAttr(string $attribute, mixed $value): bool
    {
        if (!is_string($value)) {
            $this->addError('string', $attribute);

            return false;
        } else {
            $min = 1;
            $max = 255;
            $len = strlen($value);

            if ($len < $min || $len > $max) {
                $this->addError('between.string', compact('attribute', 'min', 'max'));

                return false;
            } elseif (!preg_match('/^[A-Za-z_]$/', $value[0])) {
                $values = 'a-z, A-Z, _';
                $this->addError('starts_with', compact('attribute', 'values'));

                return false;
            }

            return true;
        }
    }

    /**
     * Validates the given filter operator.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateFilterOperator(string $attribute, mixed $value): bool
    {
        if (is_null($value)) {
            $this->addError('required', $attribute);

            return false;
        } elseif (!FilterFactory::operatorRegistered($value)) {
            $this->addError('in', $attribute);

            return false;
        }

        return true;
    }
}
