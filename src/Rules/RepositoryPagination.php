<?php

namespace LaravelRepository\Rules;

use LaravelRepository\Pagination;
use Illuminate\Contracts\Validation\Rule;

class RepositoryPagination implements Rule
{
    /**
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Creates a rule instance.
     *
     * @param  int $perPageMax
     * @param  int $perPageMin
     * @return void
     */
    public function __construct(
        protected int $perPageMax = 1000,
        protected int $perPageMin = 1
    ) {
        //
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
        } elseif ($params = Pagination::parseStr($value)) {
            $this->validatePage($params[0], $attribute);
            $this->validatePerPage($params[1], $attribute);
        } else {
            $this->errors[] = __('lrepo::validation.repository_pagination', compact('attribute'));
        }

        return empty($this->errors);
    }

    /**
     * Get the validation error message.
     *
     * @return string
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
     * Validate the page parameter.
     *
     * @param  int $page
     * @param  string $attribute
     * @return void
     */
    protected function validatePage(int $page, string $attribute): void
    {
        if ($page < 1) {
            $this->errors[] = __('lrepo::validation.min.numeric', [
                'attribute' => "{$attribute}.page",
                'min' => 1,
            ]);
        }
    }

    /**
     * Validate the per page parameter.
     *
     * @param  int $perPage
     * @param  string $attribute
     * @return void
     */
    protected function validatePerPage(int $perPage, string $attribute): void
    {
        if ($perPage < $this->perPageMin || $perPage > $this->perPageMax) {
            $this->errors[] = __('lrepo::validation.between.numeric', [
                'attribute' => "{$attribute}.perPage",
                'min' => $this->perPageMin,
                'max' => $this->perPageMax,
            ]);
        }
    }
}
