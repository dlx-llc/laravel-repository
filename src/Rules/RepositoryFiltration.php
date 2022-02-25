<?php

namespace LaravelRepository\Rules;

use LaravelRepository\FilterFactory;
use LaravelRepository\SearchCriteria;
use LaravelRepository\Enums\FilterMode;
use Illuminate\Contracts\Validation\Rule;
use LaravelRepository\Enums\FilterGroupMode;

class RepositoryFiltration implements Rule
{
    /**
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->modes = FilterMode::cases();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            $this->errors[] = __('lrepo::validation.string', compact('attribute'));
        } elseif (empty($value)) {
            $this->errors[] = __('lrepo::validation.required', compact('attribute'));
        } elseif ($value = SearchCriteria::parseFiltrationStr($value)) {
            $this->validateFiltersArray($value, $attribute);
        } else {
            $this->errors[] = __('lrepo::validation.json', compact('attribute'));
        }

        return empty($this->errors);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        if (count($this->errors) === 1) {
            return $this->errors[0];
        } else {
            return $this->errors;
        }
    }

    /**
     * Determines if a valid filters array is given.
     *
     * @param  mixed $items
     * @param  string $attribute
     * @return void
     */
    protected function validateFiltersArray(mixed $items, string $attribute): void
    {
        if (!is_array($items) || !array_is_list($items)) {
            $this->errors[] = __('lrepo::validation.repository_filters', compact('attribute'));
        } else {
            foreach ($items as $i => $item) {
                $this->validateFilter($item, "{$attribute}.{$i}");
            }
        }
    }

    /**
     * Determines if a valid filter/filter-group is given.
     *
     * @param  mixed $item
     * @param  string $attribute
     * @return void
     */
    protected function validateFilter(mixed $item, string $attribute): void
    {
        if (!is_array($item)) {
            $this->errors[] = __('lrepo::validation.array', compact('attribute'));
        } else {
            $this->validateOrCond($item, $attribute);

            if (isset($item['items'])) {
                $this->validateGroupMode($item, $attribute);
                $this->validateFiltersArray($item['items'], "{$attribute}.items");

                if (isset($item['relation']) && !is_string($item['relation'])) {
                    $this->validateStringAttr($item['relation'], "{$attribute}.relation");
                }
            } else {
                $this->validateAttr($item, $attribute);

                if ($this->validateMode($item, $attribute)) {
                    $this->validateValue($item, $attribute);
                }
            }
        }
    }

    /**
     * Validates the given filter/filter-group condition.
     *
     * @param  array $filter
     * @param  string $attribute
     * @return void
     */
    protected function validateOrCond(array $filter, string $attribute): void
    {
        if (array_key_exists('orCond', $filter) && !is_bool($filter['orCond'])) {
            $attribute .= '.orCond';
            $this->errors[] = __('lrepo::validation.boolean', compact('attribute'));
        }
    }

    /**
     * Validates the given value to be a valid data attribute string.
     *
     * @param  mixed $value
     * @param  string $attribute
     * @return void
     */
    protected function validateStringAttr(mixed $value, string $attribute): void
    {
        if (!is_string($value)) {
            $this->errors[] = __('lrepo::validation.string', compact('attribute'));
        } else {
            $len = strlen($value);

            if ($len < 1 || $len > 255) {
                $this->errors[] = __('lrepo::validation.between.string', [
                    'attribute' => $attribute,
                    'min' => 1,
                    'max' => 255,
                ]);
            } elseif (!preg_match('/^[A-Za-z_]$/', $value[0])) {
                $this->errors[] = __('lrepo::validation.starts_with', [
                    'attribute' => $attribute,
                    'values' => 'a-z, A-Z, _',
                ]);
            }
        }
    }

    /**
     * Validates the given filter group mode.
     *
     * @param  array $group
     * @param  string $attribute
     * @return bool  TRUE if mode is valid, otherwise FALSE.
     */
    protected function validateGroupMode(array $group, string $attribute): bool
    {
        $attribute .= '.mode';

        if (array_key_exists('mode', $group)) {
            $values = FilterGroupMode::cases();

            if (!in_array($group['mode'], $values)) {
                $this->errors[] = __('lrepo::validation.in', compact('attribute'));
            }
        }

        return true;
    }

    /**
     * Validates the given filter data attribute.
     *
     * @param  array $filter
     * @param  string $attribute
     * @return void
     */
    protected function validateAttr(array $filter, string $attribute): void
    {
        $attribute .= '.attr';
        $mode = $filter['mode'] ?? null;
        $attr = $filter['attr'] ?? null;

        if (!in_array($mode, $this->modes)) {
            if (!is_null($attr)) {
                $this->validateStringAttr($attr, $attribute);
            }
        } elseif (!isset($attr)) {
            $this->errors[] = __('lrepo::validation.required', compact('attribute'));
        } else {
            $this->validateStringAttr($attr, $attribute);
        }
    }

    /**
     * Validates the given filter mode.
     *
     * @param  array $filter
     * @param  string $attribute
     * @return bool  TRUE if mode is valid, otherwise FALSE.
     */
    protected function validateMode(array $filter, string $attribute): bool
    {
        $attribute .= '.mode';

        if (!isset($filter['mode'])) {
            $this->errors[] = __('lrepo::validation.required', compact('attribute'));
        } elseif (!FilterFactory::instance()->isRegisteredMode($filter['mode'])) {
            $this->errors[] = __('lrepo::validation.in', compact('attribute'));
        } else {
            return true;
        }

        return false;
    }

    /**
     * Validates the given filter value.
     *
     * @param  array $filter
     * @param  string $attribute
     * @return void
     */
    protected function validateValue(array $filter, string $attribute): void
    {
        $attribute .= '.value';
        $value = $filter['value'] ?? null;
        $filterClassName = FilterFactory::instance()->getClass($filter['mode']);

        if ($errors = $filterClassName::validateValue($attribute, $value)) {
            $this->errors = [...$this->errors, ...$errors];
        }
    }
}
