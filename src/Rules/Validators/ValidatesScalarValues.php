<?php

namespace LaravelRepository\Rules\Validators;

trait ValidatesScalarValues
{
    /**
     * Checks if the given value is of a scalar type and isn't empty.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
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
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
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
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  int|null $size
     * @return bool
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

        return count($this->errors) > $errCountBefore;
    }

    /**
     * Checks if the given value is an array of scalar values.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  int|null $size
     * @return bool
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

        return count($this->errors) > $errCountBefore;
    }

    /**
     * Checks if the given value is a scalar value or an array of scalar values.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function validateScalarOrArrayOfScalar(string $attribute, mixed $value): bool
    {
        if (is_array($value)) {
            return $this->validateArrayOfScalar($attribute, $value);
        } elseif (is_scalar($value)) {
            return $this->validateScalar($attribute, $value);
        } else {
            $this->addError('array_or_scalar', $attribute);

            return false;
        }
    }
}
