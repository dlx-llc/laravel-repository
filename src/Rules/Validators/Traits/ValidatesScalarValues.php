<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules\Validators\Traits;

trait ValidatesScalarValues
{
    /**
     * Checks if the given value is of a scalar type and isn't empty.
     */
    public function validateNotEmptyScalar(string $attribute, mixed $value): bool
    {
        if (!$this->validateScalar($attribute, $value)) {
            return false;
        } elseif ($value === '') {
            $this->addError('required', $attribute);

            return false;
        }

        return true;
    }

    /**
     * Checks if the given value is of a scalar type.
     */
    public function validateScalar(string $attribute, mixed $value): bool
    {
        if (!is_scalar($value)) {
            $this->addError('scalar', $attribute);

            return false;
        }

        return true;
    }

    /**
     * Checks if the given value is an array of not empty scalar values.
     */
    public function validateArrayOfNotEmptyScalar(
        string $attribute,
        mixed $value,
        ?int $size = null
    ): bool {
        $errCountBefore = count($this->errors);

        if (!is_array($value)) {
            $this->addError('array', $attribute);
        } elseif (empty($value)) {
            $this->addError('required', $attribute);
        } else {
            if (isset($size) && count($value) !== $size) {
                $this->addError('size.array', compact('attribute', 'size'));
            }

            foreach ($value as $i => $item) {
                $this->validateNotEmptyScalar("{$attribute}.{$i}", $item);
            }
        }

        return count($this->errors) <= $errCountBefore;
    }

    /**
     * Checks if the given value is an array of scalar values.
     */
    public function validateArrayOfScalar(
        string $attribute,
        mixed $value,
        ?int $size = null
    ): bool {
        $errCountBefore = count($this->errors);

        if (!is_array($value)) {
            $this->addError('array', $attribute);
        } elseif (empty($value)) {
            $this->addError('required', $attribute);
        } else {
            if (isset($size) && count($value) !== $size) {
                $this->addError('size.array', compact('attribute', 'size'));
            }

            foreach ($value as $i => $item) {
                $this->validateScalar("{$attribute}.{$i}", $item);
            }
        }

        return count($this->errors) <= $errCountBefore;
    }

    /**
     * Checks if the given value is a scalar value or an array of scalar values.
     */
    public function validateScalarOrArrayOfScalar(string $attribute, mixed $value): bool
    {
        if (is_array($value)) {
            return $this->validateArrayOfScalar($attribute, $value);
        } elseif (is_scalar($value)) {
            return $this->validateScalar($attribute, $value);
        }

        $this->addError('array_or_scalar', $attribute);

        return false;
    }
}
